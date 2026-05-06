<!-- ══ PANBOT CHATBOT WIDGET ══ -->
<style>
    /* ── Botón flotante ── */
    #panbot-toggle {
        position: fixed;
        bottom: 28px;
        right: 28px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #F97316, #EA6A0A);
        border-radius: 50%;
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(249,115,22,0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        z-index: 1000;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    #panbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 28px rgba(249,115,22,0.55);
    }
    #panbot-toggle .panbot-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        width: 18px;
        height: 18px;
        background: #EF4444;
        border-radius: 50%;
        border: 2px solid #fff;
        font-size: 10px;
        font-weight: 900;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        display: none;
    }

    /* ── Ventana del chat ── */
    #panbot-window {
        position: fixed;
        bottom: 96px;
        right: 28px;
        width: 360px;
        max-height: 520px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow: hidden;
        transform: scale(0.85) translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1),
                    opacity 0.2s ease;
        border: 1px solid #F3D5B5;
    }
    #panbot-window.open {
        transform: scale(1) translateY(0);
        opacity: 1;
        pointer-events: all;
    }

    /* ── Header del chat ── */
    .panbot-header {
        background: linear-gradient(135deg, #F97316, #EA6A0A);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .panbot-avatar {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .panbot-header-info {
        flex: 1;
    }
    .panbot-header-info strong {
        display: block;
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        font-family: 'Nunito', sans-serif;
    }
    .panbot-header-info span {
        color: rgba(255,255,255,0.8);
        font-size: 11px;
        font-weight: 600;
    }
    .panbot-online {
        width: 8px;
        height: 8px;
        background: #4ADE80;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(74,222,128,0.3);
        animation: pulsOnline 2s ease-in-out infinite;
    }
    @keyframes pulsOnline {
        0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,0.3); }
        50%      { box-shadow: 0 0 0 5px rgba(74,222,128,0.1); }
    }
    #panbot-close {
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    #panbot-close:hover { background: rgba(255,255,255,0.25); }

    /* ── Mensajes ── */
    #panbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #FEF3E8;
    }
    #panbot-messages::-webkit-scrollbar { width: 4px; }
    #panbot-messages::-webkit-scrollbar-track { background: transparent; }
    #panbot-messages::-webkit-scrollbar-thumb { background: #F3D5B5; border-radius: 4px; }

    .panbot-msg {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        animation: msgIn 0.25s ease both;
    }
    @keyframes msgIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .panbot-msg.user { flex-direction: row-reverse; }

    .panbot-msg-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .panbot-msg.bot .panbot-msg-avatar  { background: #FFF7ED; border: 1.5px solid #F3D5B5; }
    .panbot-msg.user .panbot-msg-avatar { background: #F97316; }

    .panbot-bubble {
        max-width: 78%;
        padding: 10px 13px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.5;
        font-family: 'Nunito', sans-serif;
    }
    .panbot-msg.bot .panbot-bubble {
        background: #fff;
        color: #1C0A00;
        border: 1px solid #F3D5B5;
        border-bottom-left-radius: 4px;
    }
    .panbot-msg.user .panbot-bubble {
        background: linear-gradient(135deg, #F97316, #EA6A0A);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    /* ── Typing indicator ── */
    .panbot-typing {
        display: flex;
        gap: 4px;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #F3D5B5;
        border-radius: 16px;
        border-bottom-left-radius: 4px;
        width: fit-content;
    }
    .panbot-typing span {
        width: 7px;
        height: 7px;
        background: #F97316;
        border-radius: 50%;
        animation: typingDot 1.2s ease-in-out infinite;
    }
    .panbot-typing span:nth-child(2) { animation-delay: 0.2s; }
    .panbot-typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingDot {
        0%,60%,100% { transform: translateY(0); opacity: 0.4; }
        30%          { transform: translateY(-6px); opacity: 1; }
    }

    /* ── Sugerencias rápidas ── */
    .panbot-suggestions {
        padding: 8px 12px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        background: #FEF3E8;
        flex-shrink: 0;
    }
    .panbot-suggestion {
        background: #fff;
        border: 1px solid #F3D5B5;
        color: #EA6A0A;
        font-size: 11px;
        font-weight: 800;
        padding: 5px 10px;
        border-radius: 99px;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
        font-family: 'Nunito', sans-serif;
        white-space: nowrap;
    }
    .panbot-suggestion:hover {
        background: #FFF7ED;
        border-color: #F97316;
    }

    /* ── Input ── */
    .panbot-input-area {
        padding: 12px;
        background: #fff;
        border-top: 1px solid #F3D5B5;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }
    #panbot-input {
        flex: 1;
        border: 1.5px solid #F3D5B5;
        border-radius: 12px;
        padding: 9px 13px;
        font-size: 13px;
        font-weight: 600;
        font-family: 'Nunito', sans-serif;
        color: #1C0A00;
        background: #FEF3E8;
        outline: none;
        transition: border-color 0.2s;
        resize: none;
    }
    #panbot-input:focus { border-color: #F97316; background: #fff; }
    #panbot-input::placeholder { color: #A87D5C; }
    #panbot-send {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, #F97316, #EA6A0A);
        border: none;
        border-radius: 11px;
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s, box-shadow 0.15s;
        flex-shrink: 0;
    }
    #panbot-send:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(249,115,22,0.4);
    }
    #panbot-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    @media (max-width: 480px) {
        #panbot-window { width: calc(100vw - 32px); right: 16px; bottom: 88px; }
        #panbot-toggle { right: 16px; bottom: 16px; }
    }
</style>

<!-- Botón flotante -->
<button id="panbot-toggle" title="Abrir asistente PanBot">
    🥐
    <span class="panbot-badge" id="panbot-badge">1</span>
</button>

<!-- Ventana del chat -->
<div id="panbot-window">

    <!-- Header -->
    <div class="panbot-header">
        <div class="panbot-avatar">🤖</div>
        <div class="panbot-header-info">
            <strong>PanBot</strong>
            <span>Asistente de PanApp</span>
        </div>
        <div class="panbot-online"></div>
        <button id="panbot-close" title="Cerrar">✕</button>
    </div>

    <!-- Mensajes -->
    <div id="panbot-messages">
        <div class="panbot-msg bot">
            <div class="panbot-msg-avatar">🤖</div>
            <div class="panbot-bubble">
                ¡Hola <?= htmlspecialchars(explode(' ', $_SESSION['usuario']['nombres'] ?? 'Usuario')[0]) ?>! 👋 Soy <strong>PanBot</strong>, tu asistente de PanApp. ¿En qué te puedo ayudar hoy?
            </div>
        </div>
    </div>

    <!-- Sugerencias rápidas -->
    <div class="panbot-suggestions" id="panbot-suggestions">
        <button class="panbot-suggestion">¿Cómo registro una venta?</button>
        <button class="panbot-suggestion">¿Cómo ajusto el inventario?</button>
        <button class="panbot-suggestion">¿Cómo creo un producto?</button>
        <button class="panbot-suggestion">Ver reportes</button>
        <button class="panbot-suggestion">¿Qué puedo hacer?</button>
    </div>

    <!-- Input -->
    <div class="panbot-input-area">
        <input type="text" id="panbot-input" placeholder="Escribe tu pregunta..." maxlength="300" autocomplete="off">
        <button id="panbot-send" title="Enviar">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>

</div>

<script>
(function() {
    var toggle    = document.getElementById('panbot-toggle');
    var window_   = document.getElementById('panbot-window');
    var closeBtn  = document.getElementById('panbot-close');
    var input     = document.getElementById('panbot-input');
    var sendBtn   = document.getElementById('panbot-send');
    var messages  = document.getElementById('panbot-messages');
    var badge     = document.getElementById('panbot-badge');
    var suggs     = document.getElementById('panbot-suggestions');
    var isOpen    = false;
    var isLoading = false;

    // Mostrar badge al inicio
    badge.style.display = 'flex';

    function openChat() {
        isOpen = true;
        window_.classList.add('open');
        toggle.style.transform = 'scale(0.9)';
        badge.style.display = 'none';
        setTimeout(function() { input.focus(); }, 250);
    }

    function closeChat() {
        isOpen = false;
        window_.classList.remove('open');
        toggle.style.transform = '';
    }

    toggle.addEventListener('click', function() {
        isOpen ? closeChat() : openChat();
    });
    closeBtn.addEventListener('click', closeChat);

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(text, type) {
        var div = document.createElement('div');
        div.className = 'panbot-msg ' + type;
        var avatar = type === 'bot' ? '🤖' : '👤';
        div.innerHTML =
            '<div class="panbot-msg-avatar">' + avatar + '</div>' +
            '<div class="panbot-bubble">' + text + '</div>';
        messages.appendChild(div);
        scrollBottom();
    }

    function showTyping() {
        var div = document.createElement('div');
        div.className = 'panbot-msg bot';
        div.id = 'panbot-typing-row';
        div.innerHTML =
            '<div class="panbot-msg-avatar">🤖</div>' +
            '<div class="panbot-typing"><span></span><span></span><span></span></div>';
        messages.appendChild(div);
        scrollBottom();
    }

    function hideTyping() {
        var row = document.getElementById('panbot-typing-row');
        if (row) row.remove();
    }

    function sendMessage(text) {
        if (isLoading || !text.trim()) return;
        text = text.trim();

        // Ocultar sugerencias tras primer mensaje
        if (suggs) { suggs.style.display = 'none'; }

        addMessage(text, 'user');
        input.value = '';
        isLoading = true;
        sendBtn.disabled = true;
        showTyping();

        fetch('/PanApp/controllers/ChatbotController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mensaje: text })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            hideTyping();
            // Convertir saltos de línea a <br>
            var reply = (data.reply || '⚠️ Sin respuesta').replace(/\n/g, '<br>');
            addMessage(reply, 'bot');
        })
        .catch(function() {
            hideTyping();
            addMessage('⚠️ Error de conexión. Intenta de nuevo.', 'bot');
        })
        .finally(function() {
            isLoading = false;
            sendBtn.disabled = false;
            input.focus();
        });
    }

    sendBtn.addEventListener('click', function() {
        sendMessage(input.value);
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input.value);
        }
    });

    // Sugerencias rápidas
    document.querySelectorAll('.panbot-suggestion').forEach(function(btn) {
        btn.addEventListener('click', function() {
            sendMessage(btn.textContent);
        });
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (isOpen && !window_.contains(e.target) && e.target !== toggle) {
            closeChat();
        }
    });
})();
</script>
