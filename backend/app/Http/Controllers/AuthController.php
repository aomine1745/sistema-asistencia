<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterAuthRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use  JWTAuth;
use App\User;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['login', 'register']]);
    }

    public function register(RegisterUserRequest $request) {
        if ($request->rol_id == '1') {
            $key = $request->headers->get('Apikey');
            if (empty($key) || $key !== env('API_KEY')) {
                return response()->json(['message' => 'REQUEST INVALID' ]);
            }
        }

		$user = new User();
		$user->persona_id = $request->persona_id;
		$user->rol_id = $request->rol_id;
		$user->email = $request->email;
		$user->password = bcrypt($request->password);
		$user->save();

		if ($this->loginAfterSignUp) {
			return  $this->login($request);
		}

		return  response()->json([
			'status' => 'ok',
			'data' => $user
		], 200);
	}

	public function login(Request $request) {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
	}

	public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

	public function me() {
        $user = auth()->user();
        $persona = $user->persona;
        $rol = $user->rol;

        if ($user->rol->nombre === 'admin') {
            return response()->json([
                'id' => $user->id,
                'email' => $user->email,
                'nombre' => $persona->nombre_completo,
                'rol' => $rol->nombre
            ]);
        }

        if ($user->rol->nombre === 'estudiante') {
            $estudiante = $persona->estudiante;

            return response()->json([
                'id' => $user->id,
                'email' => $user->email,
                'estudiante_id' => $estudiante->id,
                'nombre' => $persona->nombre_completo,
                'rol' => $rol->nombre
            ]);
        }

        $docente = $persona->docente;

		return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'nombre' => $persona->nombre_completo,
            'docente_id' => $docente->id,
            'rol' => $rol->nombre
        ]);
    }

    protected function respondWithToken($token)
    {
        $user = auth()->user();

        $persona = $user->persona;
        $rol = $user->rol;

        if ($rol->nombre === 'admin') {
            return response()->json([
                'id' => $user->id,
                'email' => $user->email,
                'nombre' => $persona->nombre_completo,
                'rol' => $rol->nombre,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        }

        if ($rol->nombre === 'estudiante') {
            $estudiante = $persona->estudiante;

            return response()->json([
                'id' => $user->id,
                'email' => $user->email,
                'estudiante_id' => $estudiante->id,
                'nombre' => $persona->nombre_completo,
                'rol' => $rol->nombre,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        }

        $docente = $persona->docente;

		return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'nombre' => $persona->nombre_completo,
            'docente_id' => $docente->id,
            'rol' => $rol->nombre,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
