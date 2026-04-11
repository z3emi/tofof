<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateMobileToken
{
    /**
     * Authenticate mobile API requests using Sanctum personal access tokens.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $plainTextToken = $request->bearerToken();

            if (! $plainTextToken) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $resolved = $this->resolveToken($plainTextToken);
            if (! $resolved) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if (! empty($resolved['expires_at']) && now()->greaterThan($resolved['expires_at'])) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            /** @var User $user */
            $user = $resolved['user'];

            DB::table('personal_access_tokens')
                ->where('id', $resolved['token_id'])
                ->update(['last_used_at' => now()]);

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);

            return $next($request);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }

    /**
     * @return array{token_id:int, user:User, expires_at:mixed}|null
     */
    private function resolveToken(string $plainTextToken): ?array
    {
        try {
            $accessToken = PersonalAccessToken::findToken($plainTextToken);
            if ($accessToken && $accessToken->tokenable instanceof User) {
                return [
                    'token_id' => (int) $accessToken->id,
                    'user' => $accessToken->tokenable,
                    'expires_at' => $accessToken->expires_at,
                ];
            }
        } catch (Throwable $exception) {
            // Continue to manual fallback.
        }

        return $this->resolveTokenManually($plainTextToken);
    }

    /**
     * @return array{token_id:int, user:User, expires_at:mixed}|null
     */
    private function resolveTokenManually(string $plainTextToken): ?array
    {
        $id = null;
        $token = $plainTextToken;

        if (str_contains($plainTextToken, '|')) {
            [$idPart, $tokenPart] = explode('|', $plainTextToken, 2);
            if (ctype_digit((string) $idPart)) {
                $id = (int) $idPart;
            }
            $token = $tokenPart;
        }

        $query = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $token));

        if ($id !== null) {
            $query->where('id', $id);
        }

        $row = $query->first();
        if (! $row) {
            return null;
        }

        if (($row->tokenable_type ?? null) !== User::class) {
            return null;
        }

        $user = User::find($row->tokenable_id);
        if (! $user) {
            return null;
        }

        return [
            'token_id' => (int) $row->id,
            'user' => $user,
            'expires_at' => $row->expires_at,
        ];
    }
}
