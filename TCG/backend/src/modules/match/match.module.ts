import { Module } from "@nestjs/common";
import { MatchService } from "./match.service";
import { MatchController } from "./match.controller";
import { PrismaModule } from "../../common/prisma/prisma.module";
import { InventoryModule } from "../inventory/inventory.module";

@Module({
  imports: [PrismaModule, InventoryModule],
  providers: [MatchService],
  controllers: [MatchController],
  exports: [MatchService],
})
export class MatchModule {}
