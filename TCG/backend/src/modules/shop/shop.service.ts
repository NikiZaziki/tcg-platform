import { Injectable, BadRequestException, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";

interface OrderItem {
  packId: string;
  quantity: number;
  price: number;
}

@Injectable()
export class ShopService {
  constructor(private prisma: PrismaService) {}

  async getAvailablePacks() {
    return this.prisma.pack.findMany({
      include: {
        tcg: true,
        dropTables: true,
      },
    });
  }

  async createOrder(userId: string, items: OrderItem[]) {
    if (!items || items.length === 0) {
      throw new BadRequestException("Order must contain at least one item");
    }

    let totalPrice = 0;
    const orderItems = [];

    for (const item of items) {
      const pack = await this.prisma.pack.findUnique({
        where: { id: item.packId },
      });

      if (!pack) {
        throw new NotFoundException(`Pack with id ${item.packId} not found`);
      }

      const itemTotal = pack.price * item.quantity;
      totalPrice += itemTotal;

      orderItems.push({
        packId: item.packId,
        quantity: item.quantity,
        price: pack.price,
      });
    }

    const order = await this.prisma.order.create({
      data: {
        userId,
        totalPrice,
        status: "completed",
        items: {
          create: orderItems,
        },
      },
      include: {
        items: {
          include: {
            pack: true,
          },
        },
      },
    });

    return order;
  }

  async getUserOrders(userId: string) {
    return this.prisma.order.findMany({
      where: { userId },
      include: {
        items: {
          include: {
            pack: true,
          },
        },
      },
      orderBy: {
        createdAt: "desc",
      },
    });
  }

  async getOrder(userId: string, orderId: string) {
    const order = await this.prisma.order.findUnique({
      where: { id: orderId },
      include: {
        items: {
          include: {
            pack: true,
          },
        },
      },
    });

    if (!order) {
      throw new NotFoundException("Order not found");
    }

    if (order.userId !== userId) {
      throw new BadRequestException("You do not have permission to view this order");
    }

    return order;
  }
}
