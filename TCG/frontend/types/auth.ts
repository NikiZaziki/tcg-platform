export interface User {
  id: string;
  email: string;
  username: string;
  eloRating: number;
  rankTier: string;
  createdAt: string;
  lastLogin?: string;
  lastDailyPack?: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  email: string;
  username: string;
  password: string;
}
