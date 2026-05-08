"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function RewardsPage() {
  const router = useRouter();
  const [canClaim, setCanClaim] = useState(false);
  const [lastClaimTime, setLastClaimTime] = useState<string | null>(null);
  const [nextClaimTime, setNextClaimTime] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [claiming, setClaiming] = useState(false);
  const [claimedCards, setClaimedCards] = useState<any[]>([]);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    checkDailyReward();
  }, [router]);

  const checkDailyReward = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/rewards/daily", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setCanClaim(data.canClaim);
        setLastClaimTime(data.lastClaimTime);
        setNextClaimTime(data.nextClaimTime);
      }
    } catch (error) {
      console.error("Failed to check daily reward:", error);
    } finally {
      setLoading(false);
    }
  };

  const claimDailyReward = async () => {
    setClaiming(true);
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/rewards/daily/claim", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setClaimedCards(data.cards || []);
        setCanClaim(false);
        setLastClaimTime(new Date().toISOString());

        const nextClaim = new Date();
        nextClaim.setHours(nextClaim.getHours() + 24);
        setNextClaimTime(nextClaim.toISOString());
      }
    } catch (error) {
      console.error("Failed to claim daily reward:", error);
    } finally {
      setClaiming(false);
    }
  };

  const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
  };

  const getTimeUntilNextClaim = () => {
    if (!nextClaimTime) return "Loading...";

    const now = new Date();
    const next = new Date(nextClaimTime);
    const diff = next.getTime() - now.getTime();

    if (diff <= 0) return "Available now!";

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    return `${hours}h ${minutes}m ${seconds}s`;
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
        <div className="text-white text-xl">Loading rewards...</div>
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
        <h1 className="text-3xl font-bold text-white mb-8">Daily Rewards</h1>

        {claimedCards.length > 0 ? (
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-8 border border-white/20 mb-8">
            <h2 className="text-2xl font-bold text-white mb-6">Reward Claimed!</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              {claimedCards.map((card, index) => (
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
                </div>
              ))}
            </div>
            <button
              onClick={() => setClaimedCards([])}
              className="mt-6 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition-all"
            >
              Continue
            </button>
          </div>
        ) : (
          <div className="space-y-6">
            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-8 border border-white/20">
              <div className="text-center">
                <div className="mb-6">
                  <div className="text-6xl mb-4">🎁</div>
                  <h2 className="text-2xl font-bold text-white mb-2">Daily Pack Reward</h2>
                  <p className="text-white/60">
                    Claim a free booster pack every 24 hours!
                  </p>
                </div>

                {canClaim ? (
                  <button
                    onClick={claimDailyReward}
                    disabled={claiming}
                    className="bg-gradient-to-r from-green-600 to-teal-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:from-green-700 hover:to-teal-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {claiming ? "Claiming..." : "Claim Daily Pack"}
                  </button>
                ) : (
                  <div className="space-y-4">
                    <div className="bg-yellow-500/20 border border-yellow-500 rounded-lg p-4">
                      <p className="text-yellow-400 font-semibold mb-2">
                        Next reward available in:
                      </p>
                      <p className="text-white text-2xl font-bold">
                        {getTimeUntilNextClaim()}
                      </p>
                    </div>
                    <p className="text-white/60">
                      Last claimed: {lastClaimTime ? formatTime(lastClaimTime) : "Never"}
                    </p>
                  </div>
                )}
              </div>
            </div>

            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
              <h3 className="text-xl font-bold text-white mb-4">Reward Information</h3>
              <div className="space-y-3 text-white/80">
                <div className="flex items-start">
                  <span className="text-2xl mr-3">📦</span>
                  <div>
                    <p className="font-semibold">Free Booster Pack</p>
                    <p className="text-sm">Get a random booster pack containing cards from various rarities</p>
                  </div>
                </div>
                <div className="flex items-start">
                  <span className="text-2xl mr-3">⏰</span>
                  <div>
                    <p className="font-semibold">24-Hour Cooldown</p>
                    <p className="text-sm">Claim your reward once every 24 hours</p>
                  </div>
                </div>
                <div className="flex items-start">
                  <span className="text-2xl mr-3">🎲</span>
                  <div>
                    <p className="font-semibold">Random Cards</p>
                    <p className="text-sm">Each pack contains random cards with different rarity chances</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
              <h3 className="text-xl font-bold text-white mb-4">Rarity Chances</h3>
              <div className="space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-white/80">Common</span>
                  <span className="text-white font-semibold">60%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div className="bg-gray-500 h-2 rounded-full" style={{ width: "60%" }}></div>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-white/80">Uncommon</span>
                  <span className="text-white font-semibold">25%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div className="bg-green-500 h-2 rounded-full" style={{ width: "25%" }}></div>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-white/80">Rare</span>
                  <span className="text-white font-semibold">12%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div className="bg-blue-500 h-2 rounded-full" style={{ width: "12%" }}></div>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-white/80">Ultra Rare</span>
                  <span className="text-white font-semibold">3%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div className="bg-purple-500 h-2 rounded-full" style={{ width: "3%" }}></div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}