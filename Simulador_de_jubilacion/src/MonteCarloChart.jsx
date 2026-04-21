import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import './index.css';

const CustomTooltip = ({ active, payload, label, medianKey }) => {
  if (active && payload && payload.length && medianKey) {
    const medianPayload = payload.find(p => p.dataKey === medianKey);
    if (!medianPayload) return null;
    return (
      <div style={{ backgroundColor: '#111', border: '1px solid #333', padding: '10px', borderRadius: '6px' }}>
        <div style={{ color: '#888', marginBottom: '4px' }}>Edad: {label}</div>
        <div style={{ color: '#ffcc00' }}>
          Mediana: <strong>{Math.round(medianPayload.value).toLocaleString('es-ES')} €</strong>
        </div>
      </div>
    );
  }
  return null;
};

const ParamField = ({ label, description, value, onChange, min, max, step, format }) => (
  <div className="param-field">
    <div className="param-field__header">
      <label className="param-field__label">{label}</label>
      <span className="param-field__value">{format(value)}</span>
    </div>
    <input
      type="range"
      className="param-field__range"
      min={min}
      max={max}
      step={step}
      value={value}
      onChange={e => onChange(Number(e.target.value))}
    />
    {description && (
      <div className="param-field__description">{description}</div>
    )}
  </div>
);

const DEFAULT_PARAMS = {
  ahorros: 15000,
  ahorro_anual: 5000,
  gasto_anual: 1000,
  edad_actual: 31,
  edad_jubilacion: 65,
  edad_fin: 95,
  rentabilidad: 0.07,
  volatilidad: 0.15,
  inflacion: 0.033,
};

const API_URL = 'https://montecarlo-simulator-63u9.onrender.com/api_datos.php';

const MonteCarloChart = () => {
  const [params, setParams] = useState(DEFAULT_PARAMS);
  const [pendingParams, setPendingParams] = useState(DEFAULT_PARAMS);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedYear, setSelectedYear] = useState(null);

  const fetchData = useCallback((p) => {
    setLoading(true);
    setSelectedYear(null);
    const query = new URLSearchParams({
      ahorros:         p.ahorros,
      ahorro_anual:    p.ahorro_anual,
      gasto_anual:     p.gasto_anual,
      edad_actual:     p.edad_actual,
      edad_jubilacion: p.edad_jubilacion,
      edad_fin:        p.edad_fin,
      rentabilidad:    p.rentabilidad,
      volatilidad:     p.volatilidad,
      inflacion:       p.inflacion,
    }).toString();

    fetch(`${API_URL}?${query}`)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
        return res.json();
      })
      .then(json => { setData(json); setLoading(false); })
      .catch(err => { console.error('Fetch error:', err); setLoading(false); });
  }, []);

  useEffect(() => {
    fetchData(params);
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const handleApply = () => {
    setParams(pendingParams);
    fetchData(pendingParams);
  };

  const setPending = (key) => (val) =>
    setPendingParams(prev => ({ ...prev, [key]: val }));

  const trayectorias = useMemo(() => {
    if (!data.length) return [];
    return Object.keys(data[0]).filter(k => k.startsWith('traj_'));
  }, [data]);

  const medianKey = useMemo(() => {
    if (!trayectorias.length) return null;
    return trayectorias[Math.floor(trayectorias.length / 2)];
  }, [trayectorias]);

  const trajectoryLines = useMemo(() => {
    return trayectorias.map((id) => (
      <Line
        key={id}
        type="monotone"
        dataKey={id}
        stroke="#00ffcc"
        strokeWidth={0.3}
        dot={false}
        opacity={0.08}
        isAnimationActive={false}
        legendType="none"
      />
    ));
  }, [trayectorias]);

  const handleChartClick = useCallback((state) => {
    if (state && state.activePayload) {
      const yearData = state.activePayload[0].payload;
      const values = Object.keys(yearData)
        .filter(key => key.startsWith('traj_'))
        .map(key => yearData[key])
        .sort((a, b) => a - b);
      if (!values.length) return;
      setSelectedYear({
        age:    yearData.step,
        worst:  values[0],
        normal: values[Math.floor(values.length / 2)],
        best:   values[values.length - 1],
      });
    }
  }, []);

  const getEscenariosParaEdad = useCallback((edad) => {
    const punto = data.find(d => d.step === edad);
    if (!punto) return null;
    const valores = trayectorias.map(t => punto[t]).sort((a, b) => a - b);
    return {
      peor:   valores[0],
      normal: valores[Math.floor(valores.length / 2)],
      mejor:  valores[valores.length - 1],
    };
  }, [data, trayectorias]);

  const edadesControl = [40, 50, 60, 70, 80, 90].filter(e =>
    e > pendingParams.edad_actual && e <= pendingParams.edad_fin
  );

  const fmtPct = (n) => `${(n * 100).toFixed(1)}%`;
  const fmtEur = (n) => `${n.toLocaleString('es-ES')} €`;

  const SCENARIOS = [
    { key: 'worst',  label: 'Escenario Catastrófico', sublabel: 'Percentil mínimo', accent: '#ff4d4d' },
    { key: 'normal', label: 'Escenario Normal',        sublabel: 'Mediana (P50)',    accent: '#ffcc00' },
    { key: 'best',   label: 'Mejor Escenario',         sublabel: 'Percentil máximo', accent: '#00ffcc' },
  ];

  return (
    <div className="mc-wrapper">
      <div className="mc-header">
        <h2>Simulación de Jubilación</h2>
        <p>Ajusta los parámetros y pulsa <strong>Simular</strong> para recalcular. Haz clic en la gráfica para ver el desglose por escenarios.</p>
      </div>

      <div className="mc-params-panel">
        <ParamField label="Ahorros actuales" description="Capital que tienes ahorrado hoy" value={pendingParams.ahorros} onChange={setPending('ahorros')} min={0} max={500000} step={1000} format={fmtEur} />
        <ParamField label="Ahorro anual" description="Cuánto ahorras cada año" value={pendingParams.ahorro_anual} onChange={setPending('ahorro_anual')} min={0} max={50000} step={500} format={fmtEur} />
        <ParamField label="Gasto anual (hoy)" description="Lo que querrás gastar al jubilarte, en dinero de hoy" value={pendingParams.gasto_anual} onChange={setPending('gasto_anual')} min={0} max={100000} step={500} format={fmtEur} />
        <ParamField label="Edad actual" value={pendingParams.edad_actual} onChange={setPending('edad_actual')} min={18} max={64} step={1} format={(v) => `${v} años`} />
        <ParamField label="Edad de jubilación" value={pendingParams.edad_jubilacion} onChange={setPending('edad_jubilacion')} min={pendingParams.edad_actual + 1} max={80} step={1} format={(v) => `${v} años`} />
        <ParamField label="Rentabilidad media" description="Rentabilidad anual esperada (ej: 7% → bolsa indexada)" value={pendingParams.rentabilidad} onChange={setPending('rentabilidad')} min={0.01} max={0.20} step={0.001} format={fmtPct} />
        <ParamField label="Volatilidad (riesgo)" description="Desviación típica anual del mercado" value={pendingParams.volatilidad} onChange={setPending('volatilidad')} min={0.01} max={0.50} step={0.005} format={fmtPct} />
        <ParamField label="Inflación estimada" value={pendingParams.inflacion} onChange={setPending('inflacion')} min={0.005} max={0.10} step={0.001} format={fmtPct} />

        <div className="mc-simulate-btn-wrapper">
          <button onClick={handleApply} disabled={loading} className={`mc-simulate-btn ${loading ? 'mc-simulate-btn--loading' : 'mc-simulate-btn--idle'}`}>
            {loading ? 'Simulando...' : '▶ Simular'}
          </button>
        </div>
      </div>

      {loading ? (
        <div className="mc-loading">Cargando simulación...</div>
      ) : (
        <ResponsiveContainer width="100%" height={450}>
          <LineChart data={data} onClick={handleChartClick} style={{ cursor: 'crosshair' }}>
            <CartesianGrid strokeDasharray="3 3" stroke="#1a1a1a" vertical={false} />
            <XAxis dataKey="step" stroke="#444" tick={{ fill: '#555', fontSize: 12 }} label={{ value: 'Edad', position: 'insideBottom', offset: -5, fill: '#555', fontSize: 12 }} />
            <YAxis stroke="#444" tick={{ fill: '#555', fontSize: 12 }} tickFormatter={(value) => `${(value / 1000).toFixed(0)}k€`} />
            <Tooltip content={<CustomTooltip medianKey={medianKey} />} cursor={{ stroke: '#333', strokeWidth: 1 }} />
            {trajectoryLines}
            {medianKey && (
              <Line key="median-line" type="monotone" dataKey={medianKey} stroke="#ffffff" strokeWidth={2} dot={false} opacity={0.9} isAnimationActive={false} legendType="none" />
            )}
          </LineChart>
        </ResponsiveContainer>
      )}

      {!loading && selectedYear ? (
        <div className="mc-selected-panel">
          <div className="mc-selected-panel__header">
            <div>
              <div className="mc-selected-panel__age-label">Proyección a</div>
              <div className="mc-selected-panel__age-value">{selectedYear.age} <span className="mc-selected-panel__age-unit">años</span></div>
            </div>
            <button className="mc-selected-panel__clear-btn" onClick={() => setSelectedYear(null)}>✕ Limpiar selección</button>
          </div>
          <div className="mc-scenarios-grid">
            {SCENARIOS.map(({ key, label, sublabel, accent }) => (
              <div key={key} className="mc-scenario-card" style={{ backgroundColor: `${accent}08`, border: `1px solid ${accent}22` }}>
                <div className="mc-scenario-card__title" style={{ color: accent }}>{label}</div>
                <div className="mc-scenario-card__sublabel">{sublabel}</div>
                <div className="mc-scenario-card__amount" style={{ color: accent }}>{Math.round(selectedYear[key]).toLocaleString('es-ES')} €</div>
              </div>
            ))}
          </div>
        </div>
      ) : !loading && (
        <div className="mc-no-selection">Selecciona un punto en la gráfica para ver el análisis de escenarios</div>
      )}

      {!loading && (
        <div className="mc-stress-table-section">
          <h3>Tabla de Estrés Financiero (Proyección de Patrimonio)</h3>
          <table className="mc-stress-table">
            <thead>
              <tr>
                <th>Edad</th>
                <th>Peor Escenario</th>
                <th>Escenario Normal</th>
                <th>Mejor Escenario</th>
              </tr>
            </thead>
            <tbody>
              {edadesControl.map(edad => {
                const esc = getEscenariosParaEdad(edad);
                if (!esc) return null;
                const isNegative = esc.peor <= 0;
                return (
                  <tr key={edad}>
                    <td className="mc-stress-table__age-cell">{edad} años</td>
                    <td className={isNegative ? 'mc-stress-table__worst--negative' : 'mc-stress-table__worst--positive'}>
                      {Math.round(esc.peor).toLocaleString('es-ES')} €{' '}
                      {isNegative && <span className="mc-stress-table__worst-tag">(Agotado)</span>}
                    </td>
                    <td className="mc-stress-table__normal">{Math.round(esc.normal).toLocaleString('es-ES')} €</td>
                    <td className="mc-stress-table__best">{Math.round(esc.mejor).toLocaleString('es-ES')} €</td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default React.memo(MonteCarloChart);