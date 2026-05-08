import { Controller, Post, Delete, Get, Body, UseGuards, Request } from "@nestjs/common";
import { MatchmakingService } from "./matchmaking.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("matchmaking")
@UseGuards(JwtAuthGuard)
export class MatchmakingController {
  constructor(private matchmakingService: MatchmakingService) {}

  @Post("queue")
  async joinQueue(@Request() req, @Body() body: { tcgId: string; mode: string; deckId: string }) {
    return this.matchmakingService.joinQueue(req.user.id, body.tcgId, body.mode, body.deckId);
  }

  @Delete("queue")
  async leaveQueue(@Request() req) {
    return this.matchmakingService.leaveQueue(req.user.id);
  }

  @Get("status")
  async getQueueStatus(@Request() req) {
    return this.matchmakingService.getQueueStatus(req.user.id);
  }
}
