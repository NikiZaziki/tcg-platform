"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function CollectionPage() {
  const router = useRouter();
  const [inventory, setInventory] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState("all");

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    fetchInventory();
  }, [router]);

  const fetchInventory = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/inventory", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setInventory(data);
      }
    } catch (error) {
      console.error("Failed to fetch inventory:", error);
    } finally {
      setLoading(false);
    }
  };

  const filteredInventory = inventory.filter((item) => {
    if (filter === "all") return true;
    return item.card.rarity === filter;
  });

  const getRarityColor = (rarity: string) => {
    switch (rarity) {
      case "Common": return "bg-gray-500";
      case "Uncommon": return "bg-green-500";
      case "Rare": return "bg-blue-500";
      case "Ultra Rare": return "bg-purple-500";
      default: return "bg-gray-500";
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div className="text-white text-xl">Loading collection...</div>
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
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-white mb-4">My Collection</h1>
          
          <div className="flex space-x-2 mb-6">
            {["all", "Common", "Uncommon", "Rare", "Ultra Rare"].map((rarity) => (
              <button
                key={rarity}
                onClick={() => setFilter(rarity)}
                className={`px-4 py-2 rounded-lg transition-colors ${
                  filter === rarity
                    ? "bg-white text-purple-900"
                    : "bg-white/20 text-white hover:bg-white/30"
                }`}
              >
                {rarity}
              </button>
            ))}
          </div>

          <div className="text-white/60 mb-4">
            Total cards: {filteredInventory.reduce((sum, item) => sum + item.quantity, 0)}
          </div>
        </div>

        {filteredInventory.length === 0 ? (
          <div className="text-center text-white/60 py-12">
            <p className="text-xl mb-4">No cards in your collection yet</p>
            <Link
              href="/shop"
              className="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
            >
              Visit Shop
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            {filteredInventory.map((item) => (
              <div
                key={item.id}
                className="bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden border border-white/20 hover:border-white/40 transition-all group"
              >
                <div className={`h-2 ${getRarityColor(item.card.rarity)}`} />
                <div className="p-4">
                  <div className="aspect-square bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-lg mb-4 flex items-center justify-center">
                    <span className="text-4xl">??</span>
                  </div>
                  <h3 className="text-white font-semibold mb-1">{item.card.name}</h3>
                  <p className="text-white/60 text-sm mb-2">{item.card.rarity}</p>
                  <div className="flex items-center justify-between">
                    <span className="text-white/80 text-sm">Quantity: {item.quantity}</span>
                    <span className="text-white/60 text-xs">{item.card.type}</span>
                  </div>
                  {item.card.attack !== null && (
                    <div className="mt-2 text-white/60 text-xs">
                      ATK: {item.card.attack} | DEF: {item.card.defense}
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
