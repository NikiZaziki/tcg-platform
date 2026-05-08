import { Injectable, BadRequestException, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";
import { InventoryService } from "../inventory/inventory.service";

interface GameMove {
  playerId: string;
  cardId: string;
  position: number;
  timestamp: Date;
}

@Injectable()
export class MatchService {
  private activeMatches: Map<string, any> = new Map();

  constructor(
    private prisma: PrismaService,
    private inventoryService: InventoryService,
  ) {}

  async getUserMatches(userId: string) {
    return this.prisma.match.findMany({
      where: {
        OR: [
          { player1Id: userId },
          { player2Id: userId },
        ],
      },
      include: {
        player1: {
          select: {
            id: true,
            username: true,
          },
        },
        player2: {
          select: {
            id: true,
            username: true,
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
      orderBy: {
        createdAt: "desc",
      },
    });
  }

  async getMatch(matchId: string, userId: string) {
    const match = await this.prisma.match.findUnique({
      where: { id: matchId },
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

    if (!match) {
      throw new NotFoundException("Match not found");
    }

    if (match.player1Id !== userId && match.player2Id !== userId) {
      throw new BadRequestException("You do not have permission to view this match");
    }

    return match;
  }

  async submitMove(matchId: string, userId: string, move: GameMove) {
    const match = await this.getMatch(matchId, userId);

    if (match.status !== "active") {
      throw new BadRequestException("Match is not active");
    }

    if (match.player1Id !== userId && match.player2Id !== userId) {
      throw new BadRequestException("You are not a participant in this match");
    }

    const gameState = this.activeMatches.get(matchId) || {
      moves: [],
      currentTurn: match.player1Id,
    };

    if (gameState.currentTurn !== userId) {
      throw new BadRequestException("It is not your turn");
    }

    gameState.moves.push(move);
    gameState.currentTurn = userId === match.player1Id ? match.player2Id : match.player1Id;

    this.activeMatches.set(matchId, gameState);

    return {
      success: true,
      message: "Move submitted successfully",
      gameState,
    };
  }

  async endMatch(matchId: string, winnerId: string) {
    const match = await this.prisma.match.findUnique({
      where: { id: matchId },
    });

    if (!match) {
      throw new NotFoundException("Match not found");
    }

    if (match.status !== "active") {
      throw new BadRequestException("Match is not active");
    }

    const loserId = winnerId === match.player1Id ? match.player2Id : match.player1Id;

    const updatedMatch = await this.prisma.match.update({
      where: { id: matchId },
      data: {
        status: "finished",
        winnerId,
        endedAt: new Date(),
      },
    });

    if (match.mode === "ranked") {
      await this.processRankedTransfer(matchId, winnerId, loserId);
      await this.updateEloRatings(winnerId, loserId);
    }

    this.activeMatches.delete(matchId);

    return updatedMatch;
  }

  private async processRankedTransfer(matchId: string, winnerId: string, loserId: string) {
    const match = await this.prisma.match.findUnique({
      where: { id: matchId },
      include: {
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

    if (!match) {
      return;
    }

    const loserDeckId = loserId === match.player1Id ? match.deck1Id : match.deck2Id;
    const loserDeck = await this.prisma.deck.findUnique({
      where: { id: loserDeckId },
      include: {
        cards: {
          include: {
            card: true,
          },
        },
      },
    });

    if (!loserDeck || loserDeck.cards.length === 0) {
      return;
    }

    const randomCardIndex = Math.floor(Math.random() * loserDeck.cards.length);
    const cardToTransfer = loserDeck.cards[randomCardIndex];

    await this.inventoryService.transferCard(loserId, winnerId, cardToTransfer.cardId, 1);

    await this.prisma.rankedTransfer.create({
      data: {
        matchId,
        winnerId,
        loserId,
        cardId: cardToTransfer.cardId,
      },
    });
  }

  private async updateEloRatings(winnerId: string, loserId: string) {
    const K = 32;
    const winner = await this.prisma.user.findUnique({
      where: { id: winnerId },
    });
    const loser = await this.prisma.user.findUnique({
      where: { id: loserId },
    });

    if (!winner || !loser) {
      return;
    }

    const expectedWinner = 1 / (1 + Math.pow(10, (loser.eloRating - winner.eloRating) / 400));
    const expectedLoser = 1 / (1 + Math.pow(10, (winner.eloRating - loser.eloRating) / 400));

    const newWinnerElo = Math.round(winner.eloRating + K * (1 - expectedWinner));
    const newLoserElo = Math.round(loser.eloRating + K * (0 - expectedLoser));

    await this.prisma.user.update({
      where: { id: winnerId },
      data: { eloRating: newWinnerElo },
    });

    await this.prisma.user.update({
      where: { id: loserId },
      data: { eloRating: newLoserElo },
    });
  }

  getMatchHistory(matchId: string) {
    const gameState = this.activeMatches.get(matchId);
    return gameState ? gameState.moves : [];
  }
}
