![CI Status](https://github.com/ralmher95/montecarlo-simulator/actions/workflows/main_check.yml/badge.svg)
 # **📈 Monte Carlo Retirement Simulator**
Este proyecto es una herramienta avanzada de simulación financiera que utiliza el **Método de Monte Carlo** para proyectar la viabilidad de un plan de jubilación. A diferencia de las calculadoras lineales, este motor analiza múltiples escenarios (Peor, Normal y Mejor) basándose en variables de ahorro, gasto y rentabilidad.
## **🚀 Características Principales**
- **Motor de Simulación Probabilística:** Backend robusto en PHP (RetirementEngine.php) que procesa proyecciones financieras complejas.
- **Visualización Dinámica:** Gráficos e informes interactivos desarrollados con React.
- **Análisis de Estrés:** Tabla comparativa que muestra el comportamiento de los fondos según la edad, identificando puntos de agotamiento de capital.
- **Arquitectura Desacoplada:** API RESTful en PHP que permite una fácil integración con otros frontends.
## **🛠️ Stack Tecnológico**
- **Frontend:** React.js, Recharts (para visualización), CSS3 (BEM methodology).
- **Backend:** PHP 7.4+ (Logic Engine & API).
- **Herramientas:** GitHub Actions para CI/CD (opcional).
## **📋 Estructura del Proyecto**
Plaintext

├── api/

│   ├── api\_datos.php        # Punto de entrada de la API (Endpoints)

│   └── RetirementEngine.php # Lógica central del simulador

├── src/

│   └── components/

│       └── MonteCarloChart.js # Visualización y tablas de estrés

└── public/                  # Assets estáticos
## **🔧 Instalación y Configuración**
### **Requisitos Previos**
- Servidor Web (Apache/Nginx) con PHP 7.4 o superior.
- Node.js y npm para el desarrollo del frontend.
### **Configuración del Backend**
1. Sube los archivos de la carpeta api/ a tu servidor.
1. Asegúrate de que los encabezados CORS en api\_datos.php apunten a tu dominio de frontend:

   PHP

   header("Access-Control-Allow-Origin: \*");
### **Configuración del Frontend**
1. Clona el repositorio:

   Bash

   git clone https://github.com/tu-usuario/montecarlo-simulator.git

1. Instala las dependencias:

   Bash

   npm install

1. Inicia el servidor de desarrollo:

   Bash

   npm start
## **📖 Uso de la API**
La API acepta peticiones GET y POST. Los parámetros principales son:

|**Parámetro**|**Descripción**|**Defecto**|
| :- | :- | :- |
|ahorros|Capital inicial|15,000|
|ahorro\_anual|Ahorro recurrente cada año|5,000|
|gasto\_anual|Gasto estimado en jubilación|1,000|
|edad\_actual|Edad de inicio del plan|31|
|edad\_jubilacion|Edad objetivo para retiro|67|

**Ejemplo de respuesta JSON:**

JSON

{

`  `"status": "success",

`  `"data": {

`    `"escenarios": [...],

`    `"probabilidad\_exito": 85

`  `}

}
## **🛡️ Seguridad y Buenas Prácticas (DevOps Tips)**
- **Secrets:** Nunca subas credenciales al repositorio. Usa variables de entorno (.env).
- **Validación:** El archivo api\_datos.php ya incluye sanitización básica de tipos, pero se recomienda implementar validación de esquemas en producción.
- **CI/CD:** Se recomienda configurar un flujo de GitHub Actions para correr tests unitarios sobre RetirementEngine.php antes de cada despliegue.
## **🤝 Contribuciones**
¡Las contribuciones son lo que hacen a la comunidad de código abierto un lugar increíble!

1. Haz un **Fork** del proyecto.
1. Crea una rama para tu mejora: git checkout -b feature/MejoraIncreible.
1. Haz un **Commit** de tus cambios: git commit -m 'Add: Nueva funcionalidad'.
1. Sube los cambios a tu rama: git push origin feature/MejoraIncreible.
1. Abre un **Pull Request**.

Desarrollado con ❤️ para planificadores financieros y entusiastas de los datos.

