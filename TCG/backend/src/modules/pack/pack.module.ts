import { Module } from "@nestjs/common";
import { PackService } from "./pack.service";
import { PackController } from "./pack.controller";
import { PrismaModule } from "../../common/prisma/prisma.module";
import { InventoryModule } from "../inventory/inventory.module";

@Module({
  imports: [PrismaModule, InventoryModule],
  providers: [PackService],
  controllers: [PackController],
  exports: [PackService],
})
export class PackModule {}
