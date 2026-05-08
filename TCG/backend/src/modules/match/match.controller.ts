import { Controller, Get, Post, Body, Param, UseGuards, Request } from "@nestjs/common";
import { MatchService } from "./match.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("matches")
@UseGuards(JwtAuthGuard)
export class MatchController {
  constructor(private matchService: MatchService) {}

  @Get()
  async getUserMatches(@Request() req) {
    return this.matchService.getUserMatches(req.user.id);
  }

  @Get(":id")
  async getMatch(@Request() req, @Param("id") id: string) {
    return this.matchService.getMatch(id, req.user.id);
  }

  @Post(":id/moves")
  async submitMove(@Request() req, @Param("id") id: string, @Body() body: any) {
    return this.matchService.submitMove(id, req.user.id, body);
  }

  @Get(":id/history")
  async getMatchHistory(@Param("id") id: string) {
    return this.matchService.getMatchHistory(id);
  }

  @Post(":id/end")
  async endMatch(@Param("id") id: string, @Body() body: { winnerId: string }) {
    return this.matchService.endMatch(id, body.winnerId);
  }
}
