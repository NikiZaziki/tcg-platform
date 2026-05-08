import { Injectable, BadRequestException, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";
import { InventoryService } from "../inventory/inventory.service";

interface TradeCard {
  cardId: string;
  quantity: number;
}

@Injectable()
export class TradingService {
  constructor(
    private prisma: PrismaService,
    private inventoryService: InventoryService,
  ) {}

  async createTrade(senderId: string, receiverId: string, senderCards: TradeCard[], receiverCards: TradeCard[]) {
    if (senderId === receiverId) {
      throw new BadRequestException("Cannot trade with yourself");
    }

    const receiver = await this.prisma.user.findUnique({
      where: { id: receiverId },
    });

    if (!receiver) {
      throw new NotFoundException("Receiver not found");
    }

    for (const card of senderCards) {
      const hasCard = await this.inventoryService.hasCard(senderId, card.cardId, card.quantity);
      if (!hasCard) {
        throw new BadRequestException(`You do not have enough of card ${card.cardId}`);
      }
    }

    for (const card of receiverCards) {
      const hasCard = await this.inventoryService.hasCard(receiverId, card.cardId, card.quantity);
      if (!hasCard) {
        throw new BadRequestException(`Receiver does not have enough of card ${card.cardId}`);
      }
    }

    const trade = await this.prisma.trade.create({
      data: {
        senderId,
        receiverId,
        status: "pending",
        cards: {
          create: [
            ...senderCards.map(card => ({
              userId: senderId,
              cardId: card.cardId,
              quantity: card.quantity,
            })),
            ...receiverCards.map(card => ({
              userId: receiverId,
              cardId: card.cardId,
              quantity: card.quantity,
            })),
          ],
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

    return trade;
  }

  async getUserTrades(userId: string) {
    return this.prisma.trade.findMany({
      where: {
        OR: [
          { senderId: userId },
          { receiverId: userId },
        ],
      },
      include: {
        sender: {
          select: {
            id: true,
            username: true,
          },
        },
        receiver: {
          select: {
            id: true,
            username: true,
          },
        },
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

  async getTrade(tradeId: string, userId: string) {
    const trade = await this.prisma.trade.findUnique({
      where: { id: tradeId },
      include: {
        sender: {
          select: {
            id: true,
            username: true,
          },
        },
        receiver: {
          select: {
            id: true,
            username: true,
          },
        },
        cards: {
          include: {
            card: true,
          },
        },
      },
    });

    if (!trade) {
      throw new NotFoundException("Trade not found");
    }

    if (trade.senderId !== userId && trade.receiverId !== userId) {
      throw new BadRequestException("You do not have permission to view this trade");
    }

    return trade;
  }

  async acceptTrade(tradeId: string, userId: string) {
    const trade = await this.getTrade(tradeId, userId);

    if (trade.receiverId !== userId) {
      throw new BadRequestException("Only the receiver can accept this trade");
    }

    if (trade.status !== "pending") {
      throw new BadRequestException("Trade is not pending");
    }

    const senderCards = trade.cards.filter(c => c.userId === trade.senderId);
    const receiverCards = trade.cards.filter(c => c.userId === trade.receiverId);

    for (const card of senderCards) {
      await this.inventoryService.transferCard(trade.senderId, trade.receiverId, card.cardId, card.quantity);
    }

    for (const card of receiverCards) {
      await this.inventoryService.transferCard(trade.receiverId, trade.senderId, card.cardId, card.quantity);
    }

    const updatedTrade = await this.prisma.trade.update({
      where: { id: tradeId },
      data: { status: "accepted" },
    });

    return updatedTrade;
  }

  async rejectTrade(tradeId: string, userId: string) {
    const trade = await this.getTrade(tradeId, userId);

    if (trade.receiverId !== userId) {
      throw new BadRequestException("Only the receiver can reject this trade");
    }

    if (trade.status !== "pending") {
      throw new BadRequestException("Trade is not pending");
    }

    const updatedTrade = await this.prisma.trade.update({
      where: { id: tradeId },
      data: { status: "rejected" },
    });

    return updatedTrade;
  }

  async cancelTrade(tradeId: string, userId: string) {
    const trade = await this.getTrade(tradeId, userId);

    if (trade.senderId !== userId) {
      throw new BadRequestException("Only the sender can cancel this trade");
    }

    if (trade.status !== "pending") {
      throw new BadRequestException("Trade is not pending");
    }

    const updatedTrade = await this.prisma.trade.update({
      where: { id: tradeId },
      data: { status: "cancelled" },
    });

    return updatedTrade;
  }
}
