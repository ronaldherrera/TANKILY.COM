<?php
// inc/header.php
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/proyectos/aquanote/css/style.css">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggle = document.getElementById("menu-toggle");
            const container = document.getElementById("menu-contenedor");
            const dropdown = container?.querySelector(".menu-dropdown");

            if (toggle && container) {
                toggle.addEventListener("click", (e) => {
                    e.stopPropagation();
                    container.classList.toggle("abierto");
                    dropdown.classList.toggle("abierto");
                });

                // Cerrar al hacer clic fuera del menú
                document.addEventListener("click", (e) => {
                    if (!container.contains(e.target)) {
                        container.classList.remove("abierto");
                        dropdown.classList.remove("abierto");
                    }
                });

                // Cerrar al hacer clic en un enlace
                dropdown.querySelectorAll("a").forEach(link => {
                    link.addEventListener("click", () => {
                        container.classList.remove("abierto");
                        dropdown.classList.remove("abierto");

                    });
                });
            }
        });
    </script>
</head>
<body>
<header>
    <div class="contenedor">
    <a class="logo" href="/index.php">
        <img src="/img/logo-horizontal.svg" alt="tankily" height="40">
    </a>
    <?php 
    $page = basename($_SERVER['PHP_SELF']);
    if ($page !== 'mi_cuenta.php' && $page !== 'mi_acuario.php') : ?>
        <div class="menu-contenedor" id="menu-contenedor">
            <button id="menu-toggle" class="menu-toggle"><span></span><span></span><span></span></button>
            <div class="menu-dropdown">
                <a href="/usuarios/mis_acuarios.php">Mis cuarios</a>
                <a href="/usuarios/mi_cuenta.php">Mi cuenta</a>
                <a href="/logout.php" style="color:#AD2222">Cerrar sesión</a>
            </div>
        </div>
    <?php else: ?>
        <div class="volver-index">
            <a href="/index.php">&larr; Volver sin guardar</a>
        </div>
    <?php endif; ?>
</div>
</header>
