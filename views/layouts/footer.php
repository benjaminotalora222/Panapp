<br><br>

<footer style="background-color:#1C0A00;" class="text-gray-300 py-12">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-12">

        <!-- Columna 1: Marca -->
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                     style="background:#F97316;box-shadow:0 3px 10px rgba(249,115,22,0.35);">
                    🥐
                </div>
                <h4 class="text-white text-xl font-black">
                    Pan<span style="color:#F97316;">App</span>
                </h4>
            </div>
            <p class="text-sm leading-relaxed" style="color:#A87D5C;">
                La herramienta más completa para gestionar tu panadería. Ventas, inventario y reportes en un solo lugar.
            </p>
        </div>

        <!-- Columna 2: Enlaces rápidos -->
        <div>
            <h4 class="text-white font-black mb-4">Enlaces Rápidos</h4>
            <ul class="space-y-2 text-sm">
                <li>
                    <a href="#" class="transition-colors duration-200"
                       style="color:#A87D5C;"
                       onmouseover="this.style.color='#F97316';"
                       onmouseout="this.style.color='#A87D5C';">
                        <i class="fas fa-headset mr-2"></i> Soporte Técnico
                    </a>
                </li>
                <li>
                    <a href="#" class="transition-colors duration-200"
                       style="color:#A87D5C;"
                       onmouseover="this.style.color='#F97316';"
                       onmouseout="this.style.color='#A87D5C';">
                        <i class="fas fa-book mr-2"></i> Manual de Usuario
                    </a>
                </li>
                <li>
                    <a href="#" class="transition-colors duration-200"
                       style="color:#A87D5C;"
                       onmouseover="this.style.color='#F97316';"
                       onmouseout="this.style.color='#A87D5C';">
                        <i class="fas fa-shield-alt mr-2"></i> Política de Privacidad
                    </a>
                </li>
                <li>
                    <a href="/PanApp/public/index.php" class="transition-colors duration-200"
                       style="color:#A87D5C;"
                       onmouseover="this.style.color='#F97316';"
                       onmouseout="this.style.color='#A87D5C';">
                        <i class="fas fa-home mr-2"></i> Volver al Inicio
                    </a>
                </li>
            </ul>
        </div>

        <!-- Columna 3: Contacto -->
        <div>
            <h4 class="text-white font-black mb-4">Contacto</h4>
            <p class="text-sm mb-2" style="color:#A87D5C;">
                <i class="fas fa-envelope mr-2" style="color:#F97316;"></i>
                soporte@panapp.com
            </p>
            <p class="text-sm mb-2" style="color:#A87D5C;">
                <i class="fas fa-phone mr-2" style="color:#F97316;"></i>
                +57 300 123 4567
            </p>
            <p class="text-sm mb-4" style="color:#A87D5C;">
                <i class="fas fa-map-marker-alt mr-2" style="color:#F97316;"></i>
                Colombia 🇨🇴
            </p>
            <div class="flex space-x-4">
                <a href="#" class="text-xl transition-colors duration-200"
                   style="color:#A87D5C;"
                   onmouseover="this.style.color='#F97316';"
                   onmouseout="this.style.color='#A87D5C';">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="text-xl transition-colors duration-200"
                   style="color:#A87D5C;"
                   onmouseover="this.style.color='#F97316';"
                   onmouseout="this.style.color='#A87D5C';">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-xl transition-colors duration-200"
                   style="color:#A87D5C;"
                   onmouseover="this.style.color='#F97316';"
                   onmouseout="this.style.color='#A87D5C';">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Bottom bar -->
    <div class="max-w-7xl mx-auto px-6 mt-10 pt-8 text-center text-xs"
         style="border-top:1px solid #3D1A00;color:#A87D5C;">
        &copy; 2026 <span style="color:#F97316;font-weight:900;">PanApp</span>
        &nbsp;·&nbsp; Hecho con ❤️ para panaderías colombianas &nbsp;·&nbsp; Todos los derechos reservados.
    </div>
</footer>

<?php require_once __DIR__ . '/chatbot.php'; ?>

<script>
function toggleDark() {
    var html = document.documentElement;
    var icon = document.getElementById('dark-icon');
    var isDark = html.getAttribute('data-dark') === '1';
    if (isDark) {
        html.removeAttribute('data-dark');
        localStorage.setItem('panapp-dark', '0');
        if (icon) { icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); }
    } else {
        html.setAttribute('data-dark', '1');
        localStorage.setItem('panapp-dark', '1');
        if (icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun'); }
    }
}
(function() {
    var icon = document.getElementById('dark-icon');
    if (icon && localStorage.getItem('panapp-dark') === '1') {
        icon.classList.remove('fa-moon'); icon.classList.add('fa-sun');
    }
})();

function abrirModal(modalId, boxId) {
    var modal = document.getElementById(modalId);
    var box   = document.getElementById(boxId);
    if (!modal || !box) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        box.style.transform = 'scale(1) translateY(0)';
        box.style.opacity   = '1';
    }, 10);
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') { cerrarModal(modalId, boxId); document.removeEventListener('keydown', escHandler); }
    });
    modal.addEventListener('click', function bgHandler(e) {
        if (e.target === modal) { cerrarModal(modalId, boxId); modal.removeEventListener('click', bgHandler); }
    });
}

function cerrarModal(modalId, boxId) {
    var modal = document.getElementById(modalId);
    var box   = document.getElementById(boxId);
    if (!modal || !box) return;
    box.style.transform = 'scale(0.92) translateY(16px)';
    box.style.opacity   = '0';
    setTimeout(function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 220);
}
</script>

        </main>
    </div>
</div>
</body>
</html>
</div>
</body>
</html>
