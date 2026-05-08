import { Module } from "@nestjs/common";
import { RewardService } from "./reward.service";
import { RewardController } from "./reward.controller";
import { PrismaModule } from "../../common/prisma/prisma.module";
import { PackModule } from "../pack/pack.module";

@Module({
  imports: [PrismaModule, PackModule],
  providers: [RewardService],
  controllers: [RewardController],
  exports: [RewardService],
})
export class RewardModule {}
