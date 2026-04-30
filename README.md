![CI Status](https://github.com/ralmher95/montecarlo-simulator/actions/workflows/main_check.yml/badge.svg)
# 📈 Simulador de Jubilación — Monte Carlo

Aplicación web interactiva que proyecta la probabilidad de éxito de un plan de jubilación mediante simulaciones de Monte Carlo con miles de trayectorias de mercado aleatorias.

🔗 **Demo en vivo:** [ralmher95.github.io/montecarlo-simulator](https://ralmher95.github.io/montecarlo-simulator/)

---

## 🧠 ¿Cómo funciona?

El usuario introduce sus parámetros financieros (ahorros, rentabilidad esperada, volatilidad, inflación, edad de jubilación...) y el sistema lanza cientos de simulaciones usando **Geometric Brownian Motion (GBM)** para modelar la evolución aleatoria del mercado. El resultado es un abanico de trayectorias posibles con sus percentiles: peor caso, mediana y mejor caso.

---

## 🏗️ Arquitectura
[Usuario] → [React + Vite — GitHub Pages] → [PHP 8.1 + Docker — Render]
| Capa | Tecnología | Hosting |
|---|---|---|
| Frontend | React, Vite, CSS3 | GitHub Pages |
| Backend | PHP 8.1, Apache, Docker | Render |
| CI/CD | GitHub Actions | — |

**API endpoint:**
Parámetros: `ahorros`, `ahorro_anual`, `gasto_anual`, `edad_actual`, `edad_jubilacion`, `edad_fin`, `rentabilidad`, `volatilidad`, `inflacion`

---

## 🚀 Desarrollo local

### Frontend
```bash
git clone https://github.com/ralmher95/montecarlo-simulator.git
cd montecarlo-simulator/Simulador_de_jubilacion
npm install
npm run dev
```

### Backend
Requiere PHP local (Laragon, XAMPP) o Docker:
```bash
docker build -t montecarlo-backend .
docker run -p 80:80 montecarlo-backend
```

---

## 🔄 CI/CD

Cada push a `main` dispara el workflow de GitHub Actions que:
1. Compila la app React con Vite
2. Publica el build en GitHub Pages automáticamente

El backend en Render se redespliega automáticamente al detectar cambios.

---

## ⚠️ Nota

El backend usa una instancia gratuita de Render. La primera petición puede tardar ~30 segundos mientras el servidor arranca desde estado inactivo.

## ✍️ Autor y contacto

- [Ralmher95 – GitHub](https://github.com/ralmher95)

- Proyecto original: [montecarlo-simulator](https://github.com/ralmher95/montecarlo-simulator)

- ¿Comentarios o sugerencias? Abre un issue o un pull request. ¡Toda ayuda es bienvenida!
