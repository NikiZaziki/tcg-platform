import { Controller, Get, Post, Body, Param, UseGuards, Request } from "@nestjs/common";
import { ShopService } from "./shop.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("shop")
@UseGuards(JwtAuthGuard)
export class ShopController {
  constructor(private shopService: ShopService) {}

  @Get("packs")
  async getAvailablePacks() {
    return this.shopService.getAvailablePacks();
  }

  @Post("orders")
  async createOrder(@Request() req, @Body() body: { items: any[] }) {
    return this.shopService.createOrder(req.user.id, body.items);
  }

  @Get("orders")
  async getUserOrders(@Request() req) {
    return this.shopService.getUserOrders(req.user.id);
  }

  @Get("orders/:id")
  async getOrder(@Request() req, @Param("id") id: string) {
    return this.shopService.getOrder(req.user.id, id);
  }
}
