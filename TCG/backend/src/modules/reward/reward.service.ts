import { Injectable, BadRequestException } from "@nestjs/common";
import { PrismaService } from "../../common/prisma/prisma.service";
import { PackService } from "../pack/pack.service";

@Injectable()
export class RewardService {
  constructor(
    private prisma: PrismaService,
    private packService: PackService,
  ) {}

  async getDailyRewardStatus(userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      select: {
        lastDailyPack: true,
      },
    });

    if (!user) {
      throw new BadRequestException("User not found");
    }

    const now = new Date();
    const lastClaim = user.lastDailyPack ? new Date(user.lastDailyPack) : null;
    const canClaim = !lastClaim || this.isMoreThan24HoursAgo(lastClaim, now);

    let nextClaimTime: Date | null = null;
    if (lastClaim && !canClaim) {
      nextClaimTime = new Date(lastClaim.getTime() + 24 * 60 * 60 * 1000);
    }

    return {
      canClaim,
      lastClaim,
      nextClaimTime,
    };
  }

  async claimDailyReward(userId: string) {
    const status = await this.getDailyRewardStatus(userId);

    if (!status.canClaim) {
      throw new BadRequestException("Daily reward already claimed. Please wait until tomorrow.");
    }

    const user = await this.prisma.user.update({
      where: { id: userId },
      data: { lastDailyPack: new Date() },
    });

    const basicPack = await this.prisma.pack.findFirst({
      where: { packType: "basic" },
    });

    if (!basicPack) {
      throw new BadRequestException("No basic pack available for daily reward");
    }

    const packResult = await this.packService.openPack(userId, basicPack.id);

    return {
      success: true,
      message: "Daily reward claimed successfully",
      pack: packResult,
    };
  }

  private isMoreThan24HoursAgo(date: Date, now: Date): boolean {
    const diffInMs = now.getTime() - date.getTime();
    const diffInHours = diffInMs / (1000 * 60 * 60);
    return diffInHours >= 24;
  }
}
