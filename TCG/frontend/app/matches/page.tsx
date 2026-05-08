"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface Match {
  id: string;
  player1: { username: string };
  player2: { username: string };
  mode: string;
  status: string;
  winnerId?: string;
  createdAt: string;
}

export default function MatchesPage() {
  const router = useRouter();
  const [matches, setMatches] = useState<Match[]>([]);
  const [loading, setLoading] = useState(true);
  const [inQueue, setInQueue] = useState(false);
  const [queueMode, setQueueMode] = useState<"normal" | "ranked">("normal");
  const [queueTime, setQueueTime] = useState(0);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      router.push("/auth/login");
      return;
    }

    fetchMatches();
  }, [router]);

  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (inQueue) {
      interval = setInterval(() => {
        setQueueTime((prev) => prev + 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [inQueue]);

  const fetchMatches = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/matches", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setMatches(data);
      }
    } catch (error) {
      console.error("Failed to fetch matches:", error);
    } finally {
      setLoading(false);
    }
  };

  const joinQueue = async (mode: "normal" | "ranked") => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/matchmaking/queue", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ mode }),
      });

      if (response.ok) {
        setInQueue(true);
        setQueueMode(mode);
        setQueueTime(0);
      }
    } catch (error) {
      console.error("Failed to join queue:", error);
    }
  };

  const leaveQueue = async () => {
    try {
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:3000/matchmaking/queue", {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        setInQueue(false);
        setQueueTime(0);
      }
    } catch (error) {
      console.error("Failed to leave queue:", error);
    }
  };

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + " " + date.toLocaleTimeString();
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
        <div className="text-white text-xl">Loading matches...</div>
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
        <h1 className="text-3xl font-bold text-white mb-8">Matches</h1>

        {inQueue ? (
          <div className="bg-white/10 backdrop-blur-lg rounded-xl p-8 border border-white/20 mb-8">
            <div className="text-center">
              <h2 className="text-2xl font-bold text-white mb-4">
                {queueMode === "ranked" ? "Ranked" : "Normal"} Queue
              </h2>
              <div className="text-6xl font-bold text-white mb-4">
                {formatTime(queueTime)}
              </div>
              <p className="text-white/60 mb-6">Searching for opponent...</p>
              {queueMode === "ranked" && (
                <div className="bg-red-500/20 border border-red-500 rounded-lg p-4 mb-6">
                  <p className="text-red-400 text-sm">
                    Warning: In ranked mode, if you lose, a random card from your deck will be transferred to the winner!
                  </p>
                </div>
              )}
              <button
                onClick={leaveQueue}
                className="bg-red-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-600 transition-all"
              >
                Leave Queue
              </button>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 hover:border-white/40 transition-all">
              <h3 className="text-xl font-bold text-white mb-4">Normal Mode</h3>
              <p className="text-white/60 mb-6">
                Play casual matches without any penalties. Perfect for practice and fun!
              </p>
              <button
                onClick={() => joinQueue("normal")}
                className="w-full bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-green-700 hover:to-teal-700 transition-all"
              >
                Join Normal Queue
              </button>
            </div>

            <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 hover:border-white/40 transition-all">
              <h3 className="text-xl font-bold text-white mb-4">Ranked Mode</h3>
              <p className="text-white/60 mb-6">
                Competitive matches with ELO ranking. Warning: Loser transfers a random card to winner!
              </p>
              <button
                onClick={() => joinQueue("ranked")}
                className="w-full bg-gradient-to-r from-red-600 to-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-red-700 hover:to-orange-700 transition-all"
              >
                Join Ranked Queue
              </button>
            </div>
          </div>
        )}

        <h2 className="text-2xl font-bold text-white mb-4">Match History</h2>
        <div className="bg-white/10 backdrop-blur-lg rounded-xl border border-white/20 overflow-hidden">
          {matches.length === 0 ? (
            <div className="p-8 text-center text-white/60">
              No matches yet. Start playing to see your match history!
            </div>
          ) : (
            <div className="divide-y divide-white/10">
              {matches.map((match) => (
                <div key={match.id} className="p-6 hover:bg-white/5 transition-all">
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-4 mb-2">
                        <span className={`px-3 py-1 rounded-full text-sm font-semibold ${
                          match.mode === "ranked" ? "bg-red-500/20 text-red-400" : "bg-green-500/20 text-green-400"
                        }`}>
                          {match.mode}
                        </span>
                        <span className={`px-3 py-1 rounded-full text-sm font-semibold ${
                          match.status === "finished" ? "bg-blue-500/20 text-blue-400" : "bg-yellow-500/20 text-yellow-400"
                        }`}>
                          {match.status}
                        </span>
                      </div>
                      <div className="text-white">
                        <span className="font-semibold">{match.player1.username}</span>
                        <span className="mx-2">vs</span>
                        <span className="font-semibold">{match.player2.username}</span>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-white/60 text-sm">{formatDate(match.createdAt)}</div>
                      {match.winnerId && (
                        <div className="text-green-400 font-semibold mt-1">
                          Winner: {match.winnerId === match.player1.username ? match.player1.username : match.player2.username}
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}