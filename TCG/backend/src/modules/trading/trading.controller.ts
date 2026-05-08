import { Controller, Get, Post, Put, Delete, Body, Param, UseGuards, Request } from "@nestjs/common";
import { TradingService } from "./trading.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("trades")
@UseGuards(JwtAuthGuard)
export class TradingController {
  constructor(private tradingService: TradingService) {}

  @Get()
  async getUserTrades(@Request() req) {
    return this.tradingService.getUserTrades(req.user.id);
  }

  @Post()
  async createTrade(@Request() req, @Body() body: { receiverId: string; senderCards: any[]; receiverCards: any[] }) {
    return this.tradingService.createTrade(req.user.id, body.receiverId, body.senderCards, body.receiverCards);
  }

  @Get(":id")
  async getTrade(@Request() req, @Param("id") id: string) {
    return this.tradingService.getTrade(id, req.user.id);
  }

  @Put(":id/accept")
  async acceptTrade(@Request() req, @Param("id") id: string) {
    return this.tradingService.acceptTrade(id, req.user.id);
  }

  @Put(":id/reject")
  async rejectTrade(@Request() req, @Param("id") id: string) {
    return this.tradingService.rejectTrade(id, req.user.id);
  }

  @Delete(":id")
  async cancelTrade(@Request() req, @Param("id") id: string) {
    return this.tradingService.cancelTrade(id, req.user.id);
  }
}
