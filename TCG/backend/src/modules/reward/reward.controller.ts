import { Controller, Get, Post, UseGuards, Request } from "@nestjs/common";
import { RewardService } from "./reward.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("rewards")
@UseGuards(JwtAuthGuard)
export class RewardController {
  constructor(private rewardService: RewardService) {}

  @Get("daily")
  async getDailyRewardStatus(@Request() req) {
    return this.rewardService.getDailyRewardStatus(req.user.id);
  }

  @Post("daily/claim")
  async claimDailyReward(@Request() req) {
    return this.rewardService.claimDailyReward(req.user.id);
  }
}
