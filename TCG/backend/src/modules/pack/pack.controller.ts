import { Controller, Post, Get, Body, Param, UseGuards, Request } from "@nestjs/common";
import { PackService } from "./pack.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("packs")
@UseGuards(JwtAuthGuard)
export class PackController {
  constructor(private packService: PackService) {}

  @Post("open")
  async openPack(@Request() req, @Body() body: { packId: string }) {
    return this.packService.openPack(req.user.id, body.packId);
  }

  @Get("openings")
  async getPackOpenings(@Request() req) {
    return this.packService.getPackOpenings(req.user.id);
  }
}
