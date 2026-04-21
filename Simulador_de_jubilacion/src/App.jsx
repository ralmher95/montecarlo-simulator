import MonteCarloChart from './MonteCarloChart';
import './index.css'; 

// Busca esta línea y cámbiala:
const response = await fetch('https://montecarlo-simulator-63u9.onrender.com/api_datos.php');

function App() {
  return (

    <main>
      <MonteCarloChart />
    </main>
  );
}

export default App;