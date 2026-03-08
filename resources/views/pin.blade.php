<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Agenda Sacramental</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f4f8;
            font-family: Arial, sans-serif;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px 36px 36px;
            width: 320px;
            text-align: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .logo span { color: #6366f1; }

        .subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 28px;
        }

        .dots {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            background: transparent;
            transition: background 0.15s, border-color 0.15s;
        }

        .dot.filled {
            background: #6366f1;
            border-color: #6366f1;
        }

        .dot.error {
            background: #ef4444;
            border-color: #ef4444;
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .key {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            padding: 16px 0;
            cursor: pointer;
            transition: background 0.1s, transform 0.1s;
            user-select: none;
        }

        .key:hover { background: #e0e7ff; border-color: #6366f1; }
        .key:active { transform: scale(0.95); background: #c7d2fe; }

        .key.del {
            font-size: 15px;
            color: #64748b;
        }

        .key.zero { grid-column: 2; }

        .error-msg {
            color: #ef4444;
            font-size: 13px;
            min-height: 18px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Agenda<span>Sacramental</span></div>
        <div class="subtitle">Ingresa el código de acceso</div>

        <div class="dots" id="dots">
            <div class="dot" id="d0"></div>
            <div class="dot" id="d1"></div>
            <div class="dot" id="d2"></div>
            <div class="dot" id="d3"></div>
        </div>

        <div class="error-msg" id="errorMsg">
            @error('pin') {{ $message }} @enderror
        </div>

        <div class="keypad">
            @foreach([1,2,3,4,5,6,7,8,9] as $n)
                <button class="key" onclick="presionar('{{ $n }}')">{{ $n }}</button>
            @endforeach
            <button class="key del" onclick="borrar()">⌫</button>
            <button class="key zero" onclick="presionar('0')">0</button>
        </div>

        <form id="pinForm" method="POST" action="{{ route('pin.verify') }}" style="display:none">
            @csrf
            <input type="hidden" name="pin" id="pinInput">
        </form>
    </div>

    <script>
        let pin = '';

        @error('pin')
            document.querySelectorAll('.dot').forEach(d => d.classList.add('error'));
            setTimeout(() => {
                document.querySelectorAll('.dot').forEach(d => d.classList.remove('error'));
            }, 600);
        @enderror

        function presionar(digit) {
            if (pin.length >= 4) return;
            pin += digit;
            actualizar();
            if (pin.length === 4) {
                setTimeout(enviar, 150);
            }
        }

        function borrar() {
            pin = pin.slice(0, -1);
            actualizar();
        }

        function actualizar() {
            for (let i = 0; i < 4; i++) {
                const dot = document.getElementById('d' + i);
                dot.classList.toggle('filled', i < pin.length);
            }
        }

        function enviar() {
            document.getElementById('pinInput').value = pin;
            document.getElementById('pinForm').submit();
        }

        // Soporte teclado
        document.addEventListener('keydown', e => {
            if (e.key >= '0' && e.key <= '9') presionar(e.key);
            if (e.key === 'Backspace') borrar();
        });
    </script>
</body>
</html>
