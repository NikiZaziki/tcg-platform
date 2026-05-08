"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function DashboardPage() {
  const router = useRouter();
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem("token");
    const userData = localStorage.getItem("user");

    if (!token || !userData) {
      router.push("/auth/login");
      return;
    }

    setUser(JSON.parse(userData));
    setLoading(false);
  }, [router]);

  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    router.push("/auth/login");
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div className="text-white text-xl">Loading...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      <nav className="bg-white/10 backdrop-blur-lg border-b border-white/20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-white">TCG Platform</h1>
            </div>
            <div className="flex items-center space-x-4">
              <div className="text-white">
                <span className="font-semibold">{user?.username}</span>
                <span className="ml-2 text-white/60">({user?.rankTier})</span>
              </div>
              <button
                onClick={handleLogout}
                className="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h2 className="text-3xl font-bold text-white mb-2">Welcome back, {user?.username}!</h2>
          <p className="text-white/60">ELO Rating: {user?.eloRating}</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link href="/collection" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Collection</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              View and manage your card collection
            </p>
          </Link>

          <Link href="/decks" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Decks</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              Build and manage your decks
            </p>
          </Link>

          <Link href="/shop" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Shop</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              Buy booster packs and cards
            </p>
          </Link>

          <Link href="/matches" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Matches</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              Play against other players
            </p>
          </Link>

          <Link href="/trading" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Trading</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              Trade cards with other players
            </p>
          </Link>

          <Link href="/rewards" className="bg-white/10 backdrop-blur-lg p-6 rounded-xl border border-white/20 hover:border-white/40 transition-all group">
            <div className="flex items-center mb-4">
              <div className="w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center mr-4">
                <span className="text-2xl">??</span>
              </div>
              <h3 className="text-xl font-semibold text-white">Daily Rewards</h3>
            </div>
            <p className="text-white/60 group-hover:text-white/80 transition-colors">
              Claim your daily free pack
            </p>
          </Link>
        </div>
      </div>
    </div>
  );
}
