import { Controller, Get, Post, Put, Delete, Body, Param, UseGuards, Request } from "@nestjs/common";
import { DeckService, DeckValidation } from "./deck.service";
import { JwtAuthGuard } from "../../common/guards/jwt-auth.guard";

@Controller("decks")
@UseGuards(JwtAuthGuard)
export class DeckController {
  constructor(private deckService: DeckService) {}

  @Get()
  async getUserDecks(@Request() req) {
    return this.deckService.getUserDecks(req.user.id);
  }

  @Post()
  async createDeck(@Request() req, @Body() body: { tcgId: string; name: string }) {
    return this.deckService.createDeck(req.user.id, body.tcgId, body.name);
  }

  @Get(":id")
  async getDeck(@Request() req, @Param("id") id: string) {
    return this.deckService.getDeck(id, req.user.id);
  }

  @Put(":id")
  async updateDeck(@Request() req, @Param("id") id: string, @Body() body: { name: string }) {
    return this.deckService.updateDeck(id, req.user.id, body.name);
  }

  @Delete(":id")
  async deleteDeck(@Request() req, @Param("id") id: string) {
    return this.deckService.deleteDeck(id, req.user.id);
  }

  @Post(":id/cards")
  async addCardToDeck(
    @Request() req,
    @Param("id") id: string,
    @Body() body: { cardId: string; quantity?: number }
  ) {
    return this.deckService.addCardToDeck(id, req.user.id, body.cardId, body.quantity);
  }

  @Delete(":id/cards/:cardId")
  async removeCardFromDeck(
    @Request() req,
    @Param("id") id: string,
    @Param("cardId") cardId: string,
    @Body() body?: { quantity?: number }
  ) {
    return this.deckService.removeCardFromDeck(id, req.user.id, cardId, body?.quantity);
  }

  @Get(":id/validate")
  async validateDeck(@Request() req, @Param("id") id: string) {
    return this.deckService.validateDeck(id, req.user.id);
  }
}
