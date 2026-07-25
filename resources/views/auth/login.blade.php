<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - LOIZ ERP</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f4f6f9;
        }

        .login-box {
            width: 380px;
            margin: 8% auto;
        }

        .card {
            border-radius: 8px;
        }

        .login-title {
            font-weight: bold;
            font-size: 22px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="card shadow">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="login-title">LOIZ ERP</div>
                <small class="text-muted">Iniciar sesión</small>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           required
                           autofocus>
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox"
                           name="remember"
                           class="form-check-input"
                           id="remember">

                    <label class="form-check-label" for="remember">
                        Recordarme
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Entrar
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>