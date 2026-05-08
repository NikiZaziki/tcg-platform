import { Module } from "@nestjs/common";
import { DeckService } from "./deck.service";
import { DeckController } from "./deck.controller";
import { PrismaModule } from "../../common/prisma/prisma.module";
import { InventoryModule } from "../inventory/inventory.module";

@Module({
  imports: [PrismaModule, InventoryModule],
  providers: [DeckService],
  controllers: [DeckController],
  exports: [DeckService],
})
export class DeckModule {}
