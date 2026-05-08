import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('Starting seed...');

  // Create TCGs
  const pokemon = await prisma.tCG.upsert({
    where: { name: 'Pokemon' },
    update: {},
    create: {
      name: 'Pokemon',
      deckSize: 60,
      maxCardCopies: 4,
      rulesetVersion: '1.0',
    },
  });

  const yugioh = await prisma.tCG.upsert({
    where: { name: 'Yu-Gi-Oh' },
    update: {},
    create: {
      name: 'Yu-Gi-Oh',
      deckSize: 40,
      maxCardCopies: 3,
      rulesetVersion: '1.0',
    },
  });

  const magic = await prisma.tCG.upsert({
    where: { name: 'Magic: The Gathering' },
    update: {},
    create: {
      name: 'Magic: The Gathering',
      deckSize: 60,
      maxCardCopies: 4,
      rulesetVersion: '1.0',
    },
  });

  console.log('Created TCGs:', { pokemon, yugioh, magic });

  // Create Rarities
  const common = await prisma.rarity.upsert({
    where: { name: 'Common' },
    update: {},
    create: {
      name: 'Common',
      dropRate: 0.6,
      color: '#9CA3AF',
    },
  });

  const uncommon = await prisma.rarity.upsert({
    where: { name: 'Uncommon' },
    update: {},
    create: {
      name: 'Uncommon',
      dropRate: 0.25,
      color: '#10B981',
    },
  });

  const rare = await prisma.rarity.upsert({
    where: { name: 'Rare' },
    update: {},
    create: {
      name: 'Rare',
      dropRate: 0.12,
      color: '#3B82F6',
    },
  });

  const ultraRare = await prisma.rarity.upsert({
    where: { name: 'Ultra Rare' },
    update: {},
    create: {
      name: 'Ultra Rare',
      dropRate: 0.03,
      color: '#8B5CF6',
    },
  });

  console.log('Created Rarities:', { common, uncommon, rare, ultraRare });

  // Create Pokemon Cards
  const pokemonCards = [
    { name: 'Pikachu', rarity: 'Common', type: 'Electric', attack: 40, defense: 30, abilityText: 'Static Shock' },
    { name: 'Charizard', rarity: 'Ultra Rare', type: 'Fire', attack: 120, defense: 100, abilityText: 'Flame Burst' },
    { name: 'Blastoise', rarity: 'Rare', type: 'Water', attack: 100, defense: 120, abilityText: 'Hydro Pump' },
    { name: 'Venusaur', rarity: 'Rare', type: 'Grass', attack: 90, defense: 110, abilityText: 'Solar Beam' },
    { name: 'Mewtwo', rarity: 'Ultra Rare', type: 'Psychic', attack: 130, defense: 90, abilityText: 'Psychic Blast' },
    { name: 'Snorlax', rarity: 'Uncommon', type: 'Normal', attack: 80, defense: 140, abilityText: 'Rest' },
    { name: 'Gengar', rarity: 'Rare', type: 'Ghost', attack: 95, defense: 80, abilityText: 'Shadow Ball' },
    { name: 'Dragonite', rarity: 'Ultra Rare', type: 'Dragon', attack: 125, defense: 100, abilityText: 'Dragon Rage' },
    { name: 'Eevee', rarity: 'Common', type: 'Normal', attack: 30, defense: 40, abilityText: 'Adaptability' },
    { name: 'Lucario', rarity: 'Rare', type: 'Fighting', attack: 110, defense: 70, abilityText: 'Aura Sphere' },
  ];

  for (const cardData of pokemonCards) {
    await prisma.card.upsert({
      where: {
        id: `pokemon-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`
      },
      update: {},
      create: {
        id: `pokemon-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`,
        tcgId: pokemon.id,
        name: cardData.name,
        rarity: cardData.rarity,
        type: cardData.type,
        attack: cardData.attack,
        defense: cardData.defense,
        abilityText: cardData.abilityText,
        imageUrl: `/images/pokemon/${cardData.name.toLowerCase().replace(/\s+/g, '-')}.png`,
      },
    });
  }

  console.log('Created Pokemon cards');

  // Create Yu-Gi-Oh Cards
  const yugiohCards = [
    { name: 'Dark Magician', rarity: 'Ultra Rare', type: 'Spellcaster', attack: 2500, defense: 2100, abilityText: 'Dark Magic Attack' },
    { name: 'Blue-Eyes White Dragon', rarity: 'Ultra Rare', type: 'Dragon', attack: 3000, defense: 2500, abilityText: 'Burst Stream of Destruction' },
    { name: 'Exodia the Forbidden One', rarity: 'Ultra Rare', type: 'Spellcaster', attack: 1000, defense: 1000, abilityText: 'Exodia Obliterate' },
    { name: 'Kuriboh', rarity: 'Common', type: 'Fiend', attack: 300, defense: 200, abilityText: 'Discard' },
    { name: 'Summoned Skull', rarity: 'Rare', type: 'Fiend', attack: 2500, defense: 1200, abilityText: 'Lightning Strike' },
    { name: 'Red-Eyes Black Dragon', rarity: 'Ultra Rare', type: 'Dragon', attack: 2400, defense: 2000, abilityText: 'Inferno Fire Blast' },
    { name: 'Jinzo', rarity: 'Rare', type: 'Machine', attack: 2400, defense: 1500, abilityText: 'Trap Negation' },
    { name: 'Black Luster Soldier', rarity: 'Ultra Rare', type: 'Warrior', attack: 3000, defense: 2500, abilityText: 'Chaos Strike' },
    { name: 'Celtic Guardian', rarity: 'Common', type: 'Warrior', attack: 1400, defense: 1200, abilityText: 'Sword Strike' },
    { name: 'Gaia Knight', rarity: 'Uncommon', type: 'Warrior', attack: 2300, defense: 2100, abilityText: 'Swift Attack' },
  ];

  for (const cardData of yugiohCards) {
    await prisma.card.upsert({
      where: {
        id: `yugioh-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`
      },
      update: {},
      create: {
        id: `yugioh-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`,
        tcgId: yugioh.id,
        name: cardData.name,
        rarity: cardData.rarity,
        type: cardData.type,
        attack: cardData.attack,
        defense: cardData.defense,
        abilityText: cardData.abilityText,
        imageUrl: `/images/yugioh/${cardData.name.toLowerCase().replace(/\s+/g, '-')}.png`,
      },
    });
  }

  console.log('Created Yu-Gi-Oh cards');

  // Create Magic Cards
  const magicCards = [
    { name: 'Black Lotus', rarity: 'Ultra Rare', type: 'Artifact', attack: null, defense: null, abilityText: 'Add 3 mana of any color' },
    { name: 'Ancestral Recall', rarity: 'Ultra Rare', type: 'Instant', attack: null, defense: null, abilityText: 'Draw 3 cards' },
    { name: 'Lightning Bolt', rarity: 'Common', type: 'Instant', attack: null, defense: null, abilityText: 'Deal 3 damage' },
    { name: 'Counterspell', rarity: 'Uncommon', type: 'Instant', attack: null, defense: null, abilityText: 'Counter target spell' },
    { name: 'Shock', rarity: 'Common', type: 'Instant', attack: null, defense: null, abilityText: 'Deal 2 damage' },
    { name: 'Giant Growth', rarity: 'Common', type: 'Instant', attack: null, defense: null, abilityText: 'Target creature gets +3/+3' },
    { name: 'Dark Ritual', rarity: 'Uncommon', type: 'Instant', attack: null, defense: null, abilityText: 'Add BB mana' },
    { name: 'Mana Crypt', rarity: 'Ultra Rare', type: 'Artifact', attack: null, defense: null, abilityText: 'Add 2 colorless mana' },
    { name: 'Sol Ring', rarity: 'Rare', type: 'Artifact', attack: null, defense: null, abilityText: 'Add 2 colorless mana' },
    { name: 'Brainstorm', rarity: 'Common', type: 'Instant', attack: null, defense: null, abilityText: 'Draw 3 cards, put 2 back' },
  ];

  for (const cardData of magicCards) {
    await prisma.card.upsert({
      where: {
        id: `magic-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`
      },
      update: {},
      create: {
        id: `magic-${cardData.name.toLowerCase().replace(/\s+/g, '-')}`,
        tcgId: magic.id,
        name: cardData.name,
        rarity: cardData.rarity,
        type: cardData.type,
        attack: cardData.attack,
        defense: cardData.defense,
        abilityText: cardData.abilityText,
        imageUrl: `/images/magic/${cardData.name.toLowerCase().replace(/\s+/g, '-')}.png`,
      },
    });
  }

  console.log('Created Magic cards');

  // Create Packs for each TCG
  const pokemonBasicPack = await prisma.pack.upsert({
    where: { id: 'pokemon-basic-pack' },
    update: {},
    create: {
      id: 'pokemon-basic-pack',
      tcgId: pokemon.id,
      name: 'Pokemon Basic Pack',
      price: 5.99,
      cardsPerPack: 5,
      packType: 'basic',
    },
  });

  const pokemonPremiumPack = await prisma.pack.upsert({
    where: { id: 'pokemon-premium-pack' },
    update: {},
    create: {
      id: 'pokemon-premium-pack',
      tcgId: pokemon.id,
      name: 'Pokemon Premium Pack',
      price: 12.99,
      cardsPerPack: 10,
      packType: 'premium',
    },
  });

  const yugiohBasicPack = await prisma.pack.upsert({
    where: { id: 'yugioh-basic-pack' },
    update: {},
    create: {
      id: 'yugioh-basic-pack',
      tcgId: yugioh.id,
      name: 'Yu-Gi-Oh Basic Pack',
      price: 4.99,
      cardsPerPack: 5,
      packType: 'basic',
    },
  });

  const yugiohPremiumPack = await prisma.pack.upsert({
    where: { id: 'yugioh-premium-pack' },
    update: {},
    create: {
      id: 'yugioh-premium-pack',
      tcgId: yugioh.id,
      name: 'Yu-Gi-Oh Premium Pack',
      price: 9.99,
      cardsPerPack: 9,
      packType: 'premium',
    },
  });

  const magicBasicPack = await prisma.pack.upsert({
    where: { id: 'magic-basic-pack' },
    update: {},
    create: {
      id: 'magic-basic-pack',
      tcgId: magic.id,
      name: 'Magic Basic Pack',
      price: 6.99,
      cardsPerPack: 15,
      packType: 'basic',
    },
  });

  const magicPremiumPack = await prisma.pack.upsert({
    where: { id: 'magic-premium-pack' },
    update: {},
    create: {
      id: 'magic-premium-pack',
      tcgId: magic.id,
      name: 'Magic Premium Pack',
      price: 14.99,
      cardsPerPack: 36,
      packType: 'premium',
    },
  });

  console.log('Created packs');

  // Create Drop Tables for each pack
  const packs = [
    pokemonBasicPack,
    pokemonPremiumPack,
    yugiohBasicPack,
    yugiohPremiumPack,
    magicBasicPack,
    magicPremiumPack,
  ];

  const rarities = [common, uncommon, rare, ultraRare];

  for (const pack of packs) {
    for (const rarity of rarities) {
      await prisma.packDropTable.upsert({
        where: {
          packId_rarityId: {
            packId: pack.id,
            rarityId: rarity.id,
          },
        },
        update: {},
        create: {
          packId: pack.id,
          rarityId: rarity.id,
          probability: rarity.dropRate,
        },
      });
    }
  }

  console.log('Created drop tables');
  console.log('Seed completed successfully!');
}

main()
  .catch((e) => {
    console.error(e);
    throw e;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });