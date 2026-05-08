"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface Card {
  id: string;
  name: string;
  rarity: string;
  type: string;
  quantity: number;
}

interface Trade {
  id: string;
  sender: { username: string };
  receiver: { username: string };
  status: string;
  createdAt: string;
  cards: { card: Card; quantity: number }[];
}

export default function TradingPage() {
  const router = useRouter();
  const [trades, setTrades] = useState<Trade[]>([]);
  const [cards, setCards] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateTrade, setShowCreateTrade] = useState(false);
  const [selectedCards, setSelectedCards] = useState<{ cardId: string; quantity: number }[]>([]);
  const [receiverUsername, setReceiverUsername] = useState("");

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    fetchTrades();
    fetchCards();
  }, [router]);

  const fetchTrades = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/trades", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setTrades(data);
      }
    } catch (error) {
      console.error("Failed to fetch trades:", error);
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

  const createTrade = async () => {
    if (selectedCards.length === 0 || !receiverUsername) return;

    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/trades", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          receiverUsername,
          cards: selectedCards,
        }),
      });

      if (response.ok) {
        setShowCreateTrade(false);
        setSelectedCards([]);
        setReceiverUsername("");
        fetchTrades();
      }
    } catch (error) {
      console.error("Failed to create trade:", error);
    }
  };

  const acceptTrade = async (tradeId: string) => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/trades/${tradeId}/accept`, {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        fetchTrades();
        fetchCards();
      }
    } catch (error) {
      console.error("Failed to accept trade:", error);
    }
  };

  const rejectTrade = async (tradeId: string) => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/trades/${tradeId}/reject`, {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        fetchTrades();
      }
    } catch (error) {
      console.error("Failed to reject trade:", error);
    }
  };

  const cancelTrade = async (tradeId: string) => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`http://localhost:3000/trades/${tradeId}`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        fetchTrades();
      }
    } catch (error) {
      console.error("Failed to cancel trade:", error);
    }
  };

  const toggleCardSelection = (cardId: string) => {
    const existing = selectedCards.find((sc) => sc.cardId === cardId);
    if (existing) {
      setSelectedCards(selectedCards.filter((sc) => sc.cardId !== cardId));
    } else {
      setSelectedCards([...selectedCards, { cardId, quantity: 1 }]);
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

  const getStatusColor = (status: string) => {
    switch (status) {
      case "pending": return "bg-yellow-500/20 text-yellow-400";
      case "accepted": return "bg-green-500/20 text-green-400";
      case "rejected": return "bg-red-500/20 text-red-400";
      case "cancelled": return "bg-gray-500/20 text-gray-400";
      default: return "bg-gray-500/20 text-gray-400";
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div className="text-white text-xl">Loading trades...</div>
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
          <h1 className="text-3xl font-bold text-white">Trading</h1>
          <button
            onClick={() => setShowCreateTrade(true)}
            className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
          >
            Create Trade
          </button>
        </div>

        {showCreateTrade && (
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 mb-8">
            <h2 className="text-xl font-bold text-white mb-4">Create New Trade</h2>
            <div className="space-y-4">
              <div>
                <label className="block text-white/80 mb-2">Receiver Username</label>
                <input
                  type="text"
                  value={receiverUsername}
                  onChange={(e) => setReceiverUsername(e.target.value)}
                  className="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500"
                  placeholder="Enter username"
                />
              </div>
              <div>
                <label className="block text-white/80 mb-2">Select Cards to Trade</label>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-64 overflow-y-auto">
                  {cards.map((card) => (
                    <div
                      key={card.id}
                      onClick={() => toggleCardSelection(card.id)}
                      className={`bg-white/10 rounded-lg p-3 border-2 cursor-pointer transition-all ${
                        selectedCards.find((sc) => sc.cardId === card.id)
                          ? `${getRarityColor(card.rarity)} bg-white/20`
                          : "border-white/20 hover:border-white/40"
                      }`}
                    >
                      <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-2 flex items-center justify-center">
                        <span className="text-2xl">??</span>
                      </div>
                      <h4 className="text-white text-sm font-semibold truncate">{card.name}</h4>
                      <p className="text-white/60 text-xs">{card.rarity}</p>
                      <p className="text-white/60 text-xs">x{card.quantity}</p>
                    </div>
                  ))}
                </div>
              </div>
              <div className="flex space-x-4">
                <button
                  onClick={createTrade}
                  className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
                >
                  Create Trade
                </button>
                <button
                  onClick={() => {
                    setShowCreateTrade(false);
                    setSelectedCards([]);
                    setReceiverUsername("");
                  }}
                  className="bg-white/10 text-white px-6 py-2 rounded-lg font-semibold hover:bg-white/20 transition-all"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        )}

        <h2 className="text-2xl font-bold text-white mb-4">Trade History</h2>
        <div className="space-y-4">
          {trades.length === 0 ? (
            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-8 border border-white/20 text-center text-white/60">
              No trades yet. Create your first trade to start trading cards!
            </div>
          ) : (
            trades.map((trade) => (
              <div
                key={trade.id}
                className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 hover:border-white/40 transition-all"
              >
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <div className="flex items-center space-x-3 mb-2">
                      <span className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(trade.status)}`}>
                        {trade.status}
                      </span>
                      <span className="text-white/60 text-sm">{formatDate(trade.createdAt)}</span>
                    </div>
                    <div className="text-white">
                      <span className="font-semibold">{trade.sender.username}</span>
                      <span className="mx-2">→</span>
                      <span className="font-semibold">{trade.receiver.username}</span>
                    </div>
                  </div>
                  {trade.status === "pending" && (
                    <div className="flex space-x-2">
                      <button
                        onClick={() => acceptTrade(trade.id)}
                        className="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition-all"
                      >
                        Accept
                      </button>
                      <button
                        onClick={() => rejectTrade(trade.id)}
                        className="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition-all"
                      >
                        Reject
                      </button>
                      <button
                        onClick={() => cancelTrade(trade.id)}
                        className="bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition-all"
                      >
                        Cancel
                      </button>
                    </div>
                  )}
                </div>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  {trade.cards.map((tradeCard) => (
                    <div
                      key={tradeCard.card.id}
                      className={`bg-white/10 rounded-lg p-3 border-2 ${getRarityColor(tradeCard.card.rarity)}`}
                    >
                      <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-2 flex items-center justify-center">
                        <span className="text-2xl">??</span>
                      </div>
                      <h4 className="text-white text-sm font-semibold truncate">{tradeCard.card.name}</h4>
                      <p className="text-white/60 text-xs">{tradeCard.card.rarity}</p>
                      <p className="text-white/60 text-xs">x{tradeCard.quantity}</p>
                    </div>
                  ))}
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}