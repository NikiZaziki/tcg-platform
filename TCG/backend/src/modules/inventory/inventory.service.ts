import { Injectable, NotFoundException, BadRequestException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";

@Injectable()
export class InventoryService {
  constructor(private prisma: PrismaService) {}

  async getUserInventory(userId: string) {
    return this.prisma.inventory.findMany({
      where: { userId },
      include: { card: true },
    });
  }

  async getCardQuantity(userId: string, cardId: string) {
    const inventory = await this.prisma.inventory.findUnique({
      where: {
        userId_cardId: {
          userId,
          cardId,
        },
      },
    });
    return inventory ? inventory.quantity : 0;
  }

  async addCard(userId: string, cardId: string, quantity: number = 1, source: string = "unknown") {
    if (quantity <= 0) {
      throw new BadRequestException("Quantity must be positive");
    }

    const existing = await this.prisma.inventory.findUnique({
      where: {
        userId_cardId: {
          userId,
          cardId,
        },
      },
    });

    if (existing) {
      return this.prisma.inventory.update({
        where: { id: existing.id },
        data: { quantity: { increment: quantity } },
      });
    } else {
      return this.prisma.inventory.create({
        data: {
          userId,
          cardId,
          quantity,
          source,
        },
      });
    }
  }

  async removeCard(userId: string, cardId: string, quantity: number = 1) {
    if (quantity <= 0) {
      throw new BadRequestException("Quantity must be positive");
    }

    const existing = await this.prisma.inventory.findUnique({
      where: {
        userId_cardId: {
          userId,
          cardId,
        },
      },
    });

    if (!existing) {
      throw new NotFoundException("Card not found in inventory");
    }

    if (existing.quantity < quantity) {
      throw new BadRequestException("Insufficient card quantity");
    }

    if (existing.quantity === quantity) {
      return this.prisma.inventory.delete({
        where: { id: existing.id },
      });
    } else {
      return this.prisma.inventory.update({
        where: { id: existing.id },
        data: { quantity: { decrement: quantity } },
      });
    }
  }

  async transferCard(fromUserId: string, toUserId: string, cardId: string, quantity: number = 1) {
    await this.removeCard(fromUserId, cardId, quantity);
    await this.addCard(toUserId, cardId, quantity, "trade");
    return {
      success: true,
      message: "Card transferred successfully",
    };
  }

  async hasCard(userId: string, cardId: string, quantity: number = 1) {
    const inventory = await this.prisma.inventory.findUnique({
      where: {
        userId_cardId: {
          userId,
          cardId,
        },
      },
    });
    return inventory ? inventory.quantity >= quantity : false;
  }
}
