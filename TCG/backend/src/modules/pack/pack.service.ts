import { Injectable, BadRequestException, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";
import { InventoryService } from "../inventory/inventory.service";

interface PackResult {
  cardId: string;
  cardName: string;
  rarity: string;
  imageUrl: string;
}

@Injectable()
export class PackService {
  constructor(
    private prisma: PrismaService,
    private inventoryService: InventoryService,
  ) {}

  async openPack(userId: string, packId: string) {
    const pack = await this.prisma.pack.findUnique({
      where: { id: packId },
      include: { dropTables: true },
    });

    if (!pack) {
      throw new NotFoundException("Pack not found");
    }

    const cards = await this.generatePackCards(pack);
    
    const packOpening = await this.prisma.packOpening.create({
      data: {
        userId,
        packId,
        cards: {
          create: cards.map(card => ({
            cardId: card.cardId,
          })),
        },
      },
      include: {
        cards: {
          include: {
            card: true,
          },
        },
      },
    });

    for (const card of cards) {
      await this.inventoryService.addCard(userId, card.cardId, 1, "pack");
    }

    return {
      packOpeningId: packOpening.id,
      cards: packOpening.cards.map(pc => ({
        cardId: pc.card.id,
        cardName: pc.card.name,
        rarity: pc.card.rarity,
        type: pc.card.type,
        attack: pc.card.attack,
        defense: pc.card.defense,
        abilityText: pc.card.abilityText,
        imageUrl: pc.card.imageUrl,
      })),
    };
  }

  private async generatePackCards(pack: any): Promise<PackResult[]> {
    const results: PackResult[] = [];
    const dropTables = pack.dropTables;

    for (let i = 0; i < pack.cardsPerPack; i++) {
      const rarity = this.selectRarity(dropTables);
      const card = await this.selectCardByRarity(pack.tcgId, rarity);
      
      if (card) {
        results.push({
          cardId: card.id,
          cardName: card.name,
          rarity: card.rarity,
          imageUrl: card.imageUrl,
        });
      }
    }

    return results;
  }

  private selectRarity(dropTables: any[]): string {
    const totalProbability = dropTables.reduce((sum, dt) => sum + dt.probability, 0);
    let random = Math.random() * totalProbability;
    
    for (const dropTable of dropTables) {
      random -= dropTable.probability;
      if (random <= 0) {
        return dropTable.rarity.name;
      }
    }
    
    return dropTables[0].rarity.name;
  }

  private async selectCardByRarity(tcgId: string, rarity: string) {
    const cards = await this.prisma.card.findMany({
      where: {
        tcgId,
        rarity,
      },
    });

    if (cards.length === 0) {
      return null;
    }

    const randomIndex = Math.floor(Math.random() * cards.length);
    return cards[randomIndex];
  }

  async getPackOpenings(userId: string) {
    return this.prisma.packOpening.findMany({
      where: { userId },
      include: {
        pack: true,
        cards: {
          include: {
            card: true,
          },
        },
      },
      orderBy: {
        openedAt: "desc",
      },
    });
  }
}
