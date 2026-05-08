"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function ShopPage() {
  const router = useRouter();
  const [packs, setPacks] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [openingPack, setOpeningPack] = useState<any>(null);
  const [openedCards, setOpenedCards] = useState<any[]>([]);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    fetchPacks();
  }, [router]);

  const fetchPacks = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/shop/packs", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setPacks(data);
      }
    } catch (error) {
      console.error("Failed to fetch packs:", error);
    } finally {
      setLoading(false);
    }
  };

  const openPack = async (packId: string) => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/packs/open", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ packId }),
      });

      if (response.ok) {
        const data = await response.json();
        setOpenedCards(data.cards);
        setOpeningPack(packs.find((p) => p.id === packId));
      }
    } catch (error) {
      console.error("Failed to open pack:", error);
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
        <div className="text-white text-xl">Loading shop...</div>
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
        <h1 className="text-3xl font-bold text-white mb-8">Shop</h1>

        {openingPack && openedCards.length > 0 ? (
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6">
              Pack Opening: {openingPack.name}
            </h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              {openedCards.map((card, index) => (
                <div
                  key={index}
                  className={`bg-white/10 backdrop-blur-lg rounded-xl p-4 border-2 ${getRarityColor(card.rarity)} transform transition-all hover:scale-105`}
                >
                  <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-4 flex items-center justify-center">
                    <span className="text-4xl">??</span>
                  </div>
                  <h3 className="text-white font-semibold mb-1">{card.cardName}</h3>
                  <p className="text-white/60 text-sm">{card.rarity}</p>
                  <p className="text-white/60 text-xs">{card.type}</p>
                  {card.attack !== null && (
                    <div className="mt-2 text-white/60 text-xs">
                      ATK: {card.attack} | DEF: {card.defense}
                    </div>
                  )}
                </div>
              ))}
            </div>
            <button
              onClick={() => {
                setOpeningPack(null);
                setOpenedCards([]);
              }}
              className="mt-6 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
            >
              Back to Shop
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {packs.map((pack) => (
              <div
                key={pack.id}
                className="bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden border border-white/20 hover:border-white/40 transition-all group"
              >
                <div className="p-6">
                  <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-4 flex items-center justify-center">
                    <span className="text-6xl">??</span>
                  </div>
                  <h3 className="text-white font-semibold text-xl mb-2">{pack.name}</h3>
                  <p className="text-white/60 text-sm mb-2">{pack.tcg.name}</p>
                  <p className="text-white/60 text-sm mb-4">
                    {pack.cardsPerPack} cards per pack
                  </p>
                  <div className="flex items-center justify-between">
                    <span className="text-white font-bold text-lg">${pack.price}</span>
                    <button
                      onClick={() => openPack(pack.id)}
                      className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
                    >
                      Open Pack
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
