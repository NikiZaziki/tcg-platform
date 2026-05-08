"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface Card {
  id: string;
  name: string;
  rarity: string;
  type: string;
  attack?: number;
  defense?: number;
  abilityText?: string;
  imageUrl: string;
}

interface Deck {
  id: string;
  name: string;
  tcgId: string;
  cards: { card: Card; quantity: number }[];
}

export default function DecksPage() {
  const router = useRouter();
  const [decks, setDecks] = useState<Deck[]>([]);
  const [cards, setCards] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDeck, setSelectedDeck] = useState<Deck | null>(null);
  const [deckCards, setDeckCards] = useState<{ card: Card; quantity: number }[]>([]);
  const [newDeckName, setNewDeckName] = useState("");
  const [showCreateDeck, setShowCreateDeck] = useState(false);
  const [selectedTCG, setSelectedTCG] = useState("");

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    fetchDecks();
    fetchCards();
  }, [router]);

  const fetchDecks = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/decks", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setDecks(data);
      }
    } catch (error) {
      console.error("Failed to fetch decks:", error);
    } finally {
      setLoading(false);
    }
  };

  const fetchCards = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/inventory", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setCards(data);
      }
    } catch (error) {
      console.error("Failed to fetch cards:", error);
    }
  };

  const createDeck = async () => {
    if (!newDeckName || !selectedTCG) return;

    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/decks", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          name: newDeckName,
          tcgId: selectedTCG,
        }),
      });

      if (response.ok) {
        const data = await response.json();
        setDecks([...decks, data]);
        setNewDeckName("");
        setShowCreateDeck(false);
      }
    } catch (error) {
      console.error("Failed to create deck:", error);
    }
  };

  const selectDeck = async (deckId: string) => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/decks/${deckId}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setSelectedDeck(data);
        setDeckCards(data.cards || []);
      }
    } catch (error) {
      console.error("Failed to fetch deck details:", error);
    }
  };

  const addCardToDeck = async (cardId: string) => {
    if (!selectedDeck) return;

    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/decks/${selectedDeck.id}/cards`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ cardId }),
      });

      if (response.ok) {
        const card = cards.find((c) => c.id === cardId);
        if (card) {
          const existingCard = deckCards.find((dc) => dc.card.id === cardId);
          if (existingCard) {
            setDeckCards(deckCards.map((dc) =>
              dc.card.id === cardId
                ? { ...dc, quantity: dc.quantity + 1 }
                : dc
            ));
          } else {
            setDeckCards([...deckCards, { card, quantity: 1 }]);
          }
        }
      }
    } catch (error) {
      console.error("Failed to add card to deck:", error);
    }
  };

  const removeCardFromDeck = async (cardId: string) => {
    if (!selectedDeck) return;

    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/decks/${selectedDeck.id}/cards/${cardId}`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const existingCard = deckCards.find((dc) => dc.card.id === cardId);
        if (existingCard && existingCard.quantity > 1) {
          setDeckCards(deckCards.map((dc) =>
            dc.card.id === cardId
              ? { ...dc, quantity: dc.quantity - 1 }
              : dc
          ));
        } else {
          setDeckCards(deckCards.filter((dc) => dc.card.id !== cardId));
        }
      }
    } catch (error) {
      console.error("Failed to remove card from deck:", error);
    }
  };

  const getRarityColor = (rarity: string) => {
    switch (rarity) {
      case "Common": return "border-gray-500";
      case "Uncommon": return "border-green-500";
      case "Rare": return "border-blue-500";
      case "Ultra Rare": return "border-purple-500";
      default: return "border-gray-500";
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div className="text-white text-xl">Loading decks...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      <nav className="bg-white/10 backdrop-blur-lg border-b border-white/20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <Link href="/dashboard" className="text-2xl font-bold text-white">
              TCG Platform
            </Link>
            <div className="flex items-center space-x-4">
              <Link href="/dashboard" className="text-white/60 hover:text-white">
                Dashboard
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold text-white">Deck Builder</h1>
          <button
            onClick={() => setShowCreateDeck(true)}
            className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
          >
            Create New Deck
          </button>
        </div>

        {showCreateDeck && (
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 mb-8">
            <h2 className="text-xl font-bold text-white mb-4">Create New Deck</h2>
            <div className="space-y-4">
              <div>
                <label className="block text-white/80 mb-2">Deck Name</label>
                <input
                  type="text"
                  value={newDeckName}
                  onChange={(e) => setNewDeckName(e.target.value)}
                  className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500"
                  placeholder="Enter deck name"
                />
              </div>
              <div>
                <label className="block text-white/80 mb-2">TCG</label>
                <select
                  value={selectedTCG}
                  onChange={(e) => setSelectedTCG(e.target.value)}
                  className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="">Select TCG</option>
                  <option value="pokemon">Pokemon</option>
                  <option value="yugioh">Yu-Gi-Oh</option>
                  <option value="magic">Magic: The Gathering</option>
                </select>
              </div>
              <div className="flex space-x-4">
                <button
                  onClick={createDeck}
                  className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
                >
                  Create Deck
                </button>
                <button
                  onClick={() => setShowCreateDeck(false)}
                  className="bg-white/10 text-white px-6 py-2 rounded-lg font-semibold hover:bg-white/20 transition-all"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        )}

        {!selectedDeck ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {decks.map((deck) => (
              <div
                key={deck.id}
                onClick={() => selectDeck(deck.id)}
                className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 hover:border-white/40 transition-all cursor-pointer group"
              >
                <h3 className="text-white font-semibold text-xl mb-2">{deck.name}</h3>
                <p className="text-white/60 text-sm">
                  {deck.cards?.reduce((sum, dc) => sum + dc.quantity, 0) || 0} cards
                </p>
              </div>
            ))}
          </div>
        ) : (
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-2xl font-bold text-white">{selectedDeck.name}</h2>
                <p className="text-white/60">
                  {deckCards.reduce((sum, dc) => sum + dc.quantity, 0)} cards in deck
                </p>
              </div>
              <button
                onClick={() => {
                  setSelectedDeck(null);
                  setDeckCards([]);
                }}
                className="bg-white/10 text-white px-6 py-2 rounded-lg font-semibold hover:bg-white/20 transition-all"
              >
                Back to Decks
              </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
                <h3 className="text-xl font-bold text-white mb-4">Available Cards</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                  {cards.map((card) => (
                    <div
                      key={card.id}
                      className={`bg-white/10 rounded-lg p-3 border-2 ${getRarityColor(card.rarity)} cursor-pointer hover:bg-white/20 transition-all`}
                      onClick={() => addCardToDeck(card.id)}
                    >
                      <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-2 flex items-center justify-center">
                        <span className="text-2xl">??</span>
                      </div>
                      <h4 className="text-white text-sm font-semibold truncate">{card.name}</h4>
                      <p className="text-white/60 text-xs">{card.rarity}</p>
                    </div>
                  ))}
                </div>
              </div>

              <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
                <h3 className="text-xl font-bold text-white mb-4">Current Deck</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                  {deckCards.map((deckCard) => (
                    <div
                      key={deckCard.card.id}
                      className={`bg-white/10 rounded-lg p-3 border-2 ${getRarityColor(deckCard.card.rarity)} relative`}
                    >
                      <button
                        onClick={() => removeCardFromDeck(deckCard.card.id)}
                        className="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-all"
                      >
                        -
                      </button>
                      <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-2 flex items-center justify-center">
                        <span className="text-2xl">??</span>
                      </div>
                      <h4 className="text-white text-sm font-semibold truncate">{deckCard.card.name}</h4>
                      <p className="text-white/60 text-xs">{deckCard.card.rarity}</p>
                      <p className="text-white/60 text-xs">x{deckCard.quantity}</p>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}