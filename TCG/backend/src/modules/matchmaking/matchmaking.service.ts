import { Injectable, BadRequestException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";

interface QueueEntry {
  userId: string;
  username: string;
  eloRating: number;
  tcgId: string;
  mode: string; // 'ranked' or 'unranked'
  deckId: string;
  joinedAt: Date;
}

@Injectable()
export class MatchmakingService {
  private queue: Map<string, QueueEntry> = new Map();
  private matchmakingInterval: NodeJS.Timeout | null = null;

  constructor(private prisma: PrismaService) {
    this.startMatchmaking();
  }

  async joinQueue(userId: string, tcgId: string, mode: string, deckId: string) {
    // Validate mode
    if (!['ranked', 'unranked'].includes(mode)) {
      throw new BadRequestException("Invalid mode. Must be 'ranked' or 'unranked'");
    }

    const user = await this.prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      throw new BadRequestException("User not found");
    }

    if (this.queue.has(userId)) {
      throw new BadRequestException("User already in queue");
    }

    // Validate deck exists and belongs to user
    const deck = await this.prisma.deck.findFirst({
      where: {
        id: deckId,
        userId: userId,
        tcgId: tcgId,
      },
    });

    if (!deck) {
      throw new BadRequestException("Deck not found or invalid");
    }

    // For ranked matches, check if deck has enough cards
    if (mode === 'ranked') {
      const deckCards = await this.prisma.deckCard.findMany({
        where: { deckId: deckId },
      });

      const totalCards = deckCards.reduce((sum, card) => sum + card.quantity, 0);
      if (totalCards < 10) {
        throw new BadRequestException("Deck must have at least 10 cards for ranked matches");
      }
    }

    const entry: QueueEntry = {
      userId,
      username: user.username,
      eloRating: user.eloRating,
      tcgId,
      mode,
      deckId,
      joinedAt: new Date(),
    };

    this.queue.set(userId, entry);

    return {
      success: true,
      message: `Joined ${mode} matchmaking queue`,
      position: this.queue.size,
      mode,
    };
  }

  async leaveQueue(userId: string) {
    if (!this.queue.has(userId)) {
      throw new BadRequestException("User not in queue");
    }

    this.queue.delete(userId);

    return {
      success: true,
      message: "Left matchmaking queue",
    };
  }

  getQueueStatus(userId: string) {
    const isInQueue = this.queue.has(userId);
    const position = isInQueue ? Array.from(this.queue.keys()).indexOf(userId) + 1 : -1;

    const entry = this.queue.get(userId);

    return {
      isInQueue,
      position,
      queueSize: this.queue.size,
      mode: entry?.mode || null,
      estimatedWaitTime: this.calculateEstimatedWaitTime(position),
    };
  }

  private calculateEstimatedWaitTime(position: number): number {
    // Estimate based on position (5 seconds per position)
    return position * 5;
  }

  private startMatchmaking() {
    this.matchmakingInterval = setInterval(() => {
      this.findMatches();
    }, 5000);
  }

  private async findMatches() {
    const entries = Array.from(this.queue.values());

    // Separate by mode
    const rankedEntries = entries.filter(e => e.mode === 'ranked');
    const unrankedEntries = entries.filter(e => e.mode === 'unranked');

    // Find matches for each mode
    this.findMatchesForMode(rankedEntries, 'ranked');
    this.findMatchesForMode(unrankedEntries, 'unranked');
  }

  private findMatchesForMode(entries: QueueEntry[], mode: string) {
    for (let i = 0; i < entries.length; i++) {
      for (let j = i + 1; j < entries.length; j++) {
        const player1 = entries[i];
        const player2 = entries[j];

        if (this.canMatch(player1, player2)) {
          this.createMatch(player1, player2);

          this.queue.delete(player1.userId);
          this.queue.delete(player2.userId);

          break;
        }
      }
    }
  }

  private canMatch(player1: QueueEntry, player2: QueueEntry): boolean {
    if (player1.tcgId !== player2.tcgId) {
      return false;
    }

    if (player1.mode !== player2.mode) {
      return false;
    }

    // For ranked matches, use ELO-based matching
    if (player1.mode === 'ranked') {
      const eloDifference = Math.abs(player1.eloRating - player2.eloRating);
      return eloDifference <= 100;
    }

    // For unranked matches, match anyone
    return true;
  }

  private async createMatch(player1: QueueEntry, player2: QueueEntry) {
    const match = await this.prisma.match.create({
      data: {
        player1Id: player1.userId,
        player2Id: player2.userId,
        deck1Id: player1.deckId,
        deck2Id: player2.deckId,
        tcgId: player1.tcgId,
        mode: player1.mode,
        status: "active",
      },
      include: {
        player1: {
          select: {
            id: true,
            username: true,
            eloRating: true,
            rankTier: true,
          },
        },
        player2: {
          select: {
            id: true,
            username: true,
            eloRating: true,
            rankTier: true,
          },
        },
        deck1: {
          include: {
            cards: {
              include: {
                card: true,
              },
            },
          },
        },
        deck2: {
          include: {
            cards: {
              include: {
                card: true,
              },
            },
          },
        },
      },
    });

    return match;
  }

  onModuleDestroy() {
    if (this.matchmakingInterval) {
      clearInterval(this.matchmakingInterval);
    }
  }
}
