import { Controller, Get, Param, UseGuards, Request } from "@nestjs/common";
import { InventoryService } from "./inventory.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("inventory")
@UseGuards(JwtAuthGuard)
export class InventoryController {
  constructor(private inventoryService: InventoryService) {}

  @Get()
  async getUserInventory(@Request() req) {
    return this.inventoryService.getUserInventory(req.user.id);
  }

  @Get(":cardId")
  async getCardQuantity(@Request() req, @Param("cardId") cardId: string) {
    return {
      cardId,
      quantity: await this.inventoryService.getCardQuantity(req.user.id, cardId),
    };
  }
}
