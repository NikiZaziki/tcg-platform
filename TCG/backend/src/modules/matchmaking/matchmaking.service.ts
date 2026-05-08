import { Injectable, BadRequestException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";

interface QueueEntry {
  userId: string;
  username: string;
  eloRating: number;
  tcgId: string;
  mode: string;
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
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      throw new BadRequestException("User not found");
    }

    if (this.queue.has(userId)) {
      throw new BadRequestException("User already in queue");
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
      message: "Joined matchmaking queue",
      position: this.queue.size,
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

    return {
      isInQueue,
      position,
      queueSize: this.queue.size,
    };
  }

  private startMatchmaking() {
    this.matchmakingInterval = setInterval(() => {
      this.findMatches();
    }, 5000);
  }

  private async findMatches() {
    const entries = Array.from(this.queue.values());
    
    for (let i = 0; i < entries.length; i++) {
      for (let j = i + 1; j < entries.length; j++) {
        const player1 = entries[i];
        const player2 = entries[j];

        if (this.canMatch(player1, player2)) {
          await this.createMatch(player1, player2);
          
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

    const eloDifference = Math.abs(player1.eloRating - player2.eloRating);
    return eloDifference <= 100;
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
          },
        },
        player2: {
          select: {
            id: true,
            username: true,
            eloRating: true,
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
