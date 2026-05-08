import { Module } from "@nestjs/common";
import { TradingService } from "./trading.service";
import { TradingController } from "./trading.controller";
import { PrismaModule } from "../../common/prisma/prisma.module";
import { InventoryModule } from "../inventory/inventory.module";

@Module({
  imports: [PrismaModule, InventoryModule],
  providers: [TradingService],
  controllers: [TradingController],
  exports: [TradingService],
})
export class TradingModule {}
