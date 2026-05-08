import { Injectable, BadRequestException, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";
import { InventoryService } from "../inventory/inventory.service";

export interface DeckValidation {
  isValid: boolean;
  errors: string[];
  warnings: string[];
}

@Injectable()
export class DeckService {
  constructor(
    private prisma: PrismaService,
    private inventoryService: InventoryService,
  ) {}

  async createDeck(userId: string, tcgId: string, name: string) {
    const tcg = await this.prisma.tCG.findUnique({
      where: { id: tcgId },
    });

    if (!tcg) {
      throw new NotFoundException("TCG not found");
    }

    return this.prisma.deck.create({
      data: {
        userId,
        tcgId,
        name,
      },
    });
  }

  async getUserDecks(userId: string) {
    return this.prisma.deck.findMany({
      where: { userId },
      include: {
        tcg: true,
        cards: {
          include: {
            card: true,
          },
        },
      },
      orderBy: {
        createdAt: "desc",
      },
    });
  }

  async getDeck(deckId: string, userId: string) {
    const deck = await this.prisma.deck.findUnique({
      where: { id: deckId },
      include: {
        tcg: true,
        cards: {
          include: {
            card: true,
          },
        },
      },
    });

    if (!deck) {
      throw new NotFoundException("Deck not found");
    }

    if (deck.userId !== userId) {
      throw new BadRequestException("You do not have permission to view this deck");
    }

    return deck;
  }

  async updateDeck(deckId: string, userId: string, name: string) {
    const deck = await this.prisma.deck.findUnique({
      where: { id: deckId },
    });

    if (!deck) {
      throw new NotFoundException("Deck not found");
    }

    if (deck.userId !== userId) {
      throw new BadRequestException("You do not have permission to update this deck");
    }

    return this.prisma.deck.update({
      where: { id: deckId },
      data: { name },
    });
  }

  async deleteDeck(deckId: string, userId: string) {
    const deck = await this.prisma.deck.findUnique({
      where: { id: deckId },
    });

    if (!deck) {
      throw new NotFoundException("Deck not found");
    }

    if (deck.userId !== userId) {
      throw new BadRequestException("You do not have permission to delete this deck");
    }

    return this.prisma.deck.delete({
      where: { id: deckId },
    });
  }

  async addCardToDeck(deckId: string, userId: string, cardId: string, quantity: number = 1) {
    const deck = await this.prisma.deck.findUnique({
      where: { id: deckId },
      include: { tcg: true },
    });

    if (!deck) {
      throw new NotFoundException("Deck not found");
    }

    if (deck.userId !== userId) {
      throw new BadRequestException("You do not have permission to modify this deck");
    }

    const hasCard = await this.inventoryService.hasCard(userId, cardId, quantity);
    if (!hasCard) {
      throw new BadRequestException("You do not have enough of this card");
    }

    const existing = await this.prisma.deckCard.findUnique({
      where: {
        deckId_cardId: {
          deckId,
          cardId,
        },
      },
    });

    if (existing) {
      const newQuantity = existing.quantity + quantity;
      if (newQuantity > deck.tcg.maxCardCopies) {
        throw new BadRequestException(`Cannot have more than ${deck.tcg.maxCardCopies} copies of a card`);
      }

      return this.prisma.deckCard.update({
        where: { id: existing.id },
        data: { quantity: newQuantity },
      });
    } else {
      if (quantity > deck.tcg.maxCardCopies) {
        throw new BadRequestException(`Cannot have more than ${deck.tcg.maxCardCopies} copies of a card`);
      }

      return this.prisma.deckCard.create({
        data: {
          deckId,
          cardId,
          quantity,
        },
      });
    }
  }

  async removeCardFromDeck(deckId: string, userId: string, cardId: string, quantity: number = 1) {
    const deck = await this.prisma.deck.findUnique({
      where: { id: deckId },
    });

    if (!deck) {
      throw new NotFoundException("Deck not found");
    }

    if (deck.userId !== userId) {
      throw new BadRequestException("You do not have permission to modify this deck");
    }

    const existing = await this.prisma.deckCard.findUnique({
      where: {
        deckId_cardId: {
          deckId,
          cardId,
        },
      },
    });

    if (!existing) {
      throw new NotFoundException("Card not found in deck");
    }

    if (existing.quantity <= quantity) {
      return this.prisma.deckCard.delete({
        where: { id: existing.id },
      });
    } else {
      return this.prisma.deckCard.update({
        where: { id: existing.id },
        data: { quantity: existing.quantity - quantity },
      });
    }
  }

  async validateDeck(deckId: string, userId: string): Promise<DeckValidation> {
    const deck = await this.getDeck(deckId, userId);
    const errors: string[] = [];
    const warnings: string[] = [];

    const totalCards = deck.cards.reduce((sum, dc) => sum + dc.quantity, 0);

    if (totalCards < deck.tcg.deckSize) {
      errors.push(`Deck must have exactly ${deck.tcg.deckSize} cards (currently ${totalCards})`);
    }

    if (totalCards > deck.tcg.deckSize) {
      errors.push(`Deck cannot have more than ${deck.tcg.deckSize} cards (currently ${totalCards})`);
    }

    for (const deckCard of deck.cards) {
      if (deckCard.quantity > deck.tcg.maxCardCopies) {
        errors.push(`Card "${deckCard.card.name}" exceeds maximum copy limit (${deck.tcg.maxCardCopies})`);
      }

      const hasCard = await this.inventoryService.hasCard(userId, deckCard.cardId, deckCard.quantity);
      if (!hasCard) {
        warnings.push(`You do not have enough copies of "${deckCard.card.name}" in your inventory`);
      }
    }

    return {
      isValid: errors.length === 0,
      errors,
      warnings,
    };
  }
}
