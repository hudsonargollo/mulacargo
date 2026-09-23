import React, { useState } from 'react';

const Icons = {
  Strategy: () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  ),
  Truck: () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
    </svg>
  ),
  Store: () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  ),
  Ads: () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
    </svg>
  ),
  Users: () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  )
};

const transportScriptsData = [
  {
    title: "El Hack del retorno",
    trend: "Hack educativo rápido",
    hook: "Text-on-screen: ¿Sigues volviendo vacío? Estás perdiendo plata 💸",
    visual: [
      "(0:00-0:03) Creador en la cabina del camión, con un aguayo. Mira a la cámara con frustración.",
      "(0:03-0:10) Crash zoom hacia el celular mostrando el sistema de pujas de Mula Cargo.",
      "(0:10-0:15) Sonríe, arranca el camión. Señala hacia el botón de descarga."
    ],
    audio: [
      "¿Sigues volviendo con el camión vacío? Estás perdiendo plata, pariente.",
      "Descárgate Mula Cargo. Aquí tú mismo le pones precio a la carga de vuelta con pujas.",
      "Carga segura y directo a tu ruta. Usa mi código para tu primer viaje."
    ]
  },
  {
    title: "POV: El calvario del pago",
    trend: "Sketch de comedia / Contraste",
    hook: "Text-on-screen: Cuando terminas el flete y toca cobrar 🤡 vs 😎",
    visual: [
      "(0:00-0:05) Personaje 1: Cansado, con cuaderno viejo, rogando por teléfono.",
      "(0:05-0:12) Personaje 2: Limpio, relajado. SFX: Notificación de pago alegre.",
      "(0:12-0:18) Personaje 2 señala el texto en pantalla con el código."
    ],
    audio: [
      "Ya puej caserito, deposíteme lo del flete... ¿El viernes recién? Pucha...",
      "Yo ya no ruego. Con Mula Cargo entrego la carga y el pago entra directo a mi billetera digital.",
      "Descarga la del aguayo naranja, usa mi código y maneja tranquilo."
    ]
  },
  {
    title: "El chisme rutero",
    trend: "Storytime casual",
    hook: "Text-on-screen: Cómo gané plata extra solo por avisarle a mi compadre 🤫",
    visual: [
      "(0:00-0:05) Creador limpiando el espejo retrovisor. Contacto visual casual.",
      "(0:05-0:12) Muestra el botón SOS en la app y cambia a referidos.",
      "(0:12-0:20) Sonríe, aparece texto flotante con código de invitación."
    ],
    audio: [
      "Storytime de cómo la app de la mula naranja me salvó en carretera y me pagó.",
      "Usé el botón SOS, me asistieron, y le conté esto a mi compadre...",
      "...le pasé mi código, él agarró su flete y a mí me cayó un bono. Usa mi código."
    ]
  }
];

const smesScriptsData = [
  {
    title: "El Hack para Comerciantes",
    trend: "Business Hack Directo",
    hook: "Text-on-screen: Cómo enviar tu mercadería sin que te vean la cara 📦💸",
    visual: [
      "(0:00-0:04) Creador sella una caja rápidamente (SFX fuerte de cinta).",
      "(0:04-0:12) Muestra celular con varios transportistas ofreciendo precios en vivo.",
      "(0:12-0:18) Levanta la caja, señala abajo (al link de descarga)."
    ],
    audio: [
      "Si tienes negocio, deja de aceptar el primer precio que te dan por flete.",
      "Yo subo mi carga a Mula Cargo, los choferes pujan y elijo el mejor precio.",
      "Es 100% seguro. Descárgate Mula Cargo y ponle vos el precio."
    ]
  },
  {
    title: "El Agenciero vs. La App",
    trend: "Sketch Comedia",
    hook: "Text-on-screen: Cuando vas a cotizar un flete a la agencia tradicional 🤡",
    visual: [
      "(0:00-0:06) Agenciero: Actitud despectiva, con calculadora y masticando algo.",
      "(0:06-0:12) Dueño PYME: Se ríe, saca celular (Filtro más brillante).",
      "(0:12-0:18) Dueño PYME habla directo a cámara. Texto CTA flotando."
    ],
    audio: [
      "A ver casero... te sale 3,000 bolivianos. Tómalo o déjalo, no hay camiones.",
      "No gracias. Ya la subí a Mula Cargo, tengo pujas y el flete bajó a 2,200.",
      "Comerciante inteligente no pierde plata. Bájate la app y que gane el mejor."
    ]
  },
  {
    title: "El Alivio del Dueño",
    trend: "Storytime Dramático",
    hook: "Text-on-screen: Casi pierdo 10.000 Bs por un chofer de Facebook 😱",
    visual: [
      "(0:00-0:05) Revisando inventario con cara de preocupación. Suspira.",
      "(0:05-0:15) Gesticula con dramatismo, luego muestra la app tranquilamente.",
      "(0:15-0:22) Sonríe. Gráfico en pantalla: 'Tú decides el precio'."
    ],
    audio: [
      "Storytime de cómo casi pierdo mercadería por buscar flete barato en grupos.",
      "El camión no llegó y me bloquearon. Ahora solo uso choferes verificados de Mula Cargo.",
      "Yo lanzo mi precio a las pujas y duermo tranquilo. Descárgala en mi perfil."
    ]
  }
];

const ScriptCard = ({ script }) => (
  <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div className="bg-orange-500 text-white px-6 py-4">
      <h3 className="text-xl font-bold">{script.title}</h3>
      <p className="text-orange-100 text-sm mt-1">{script.trend}</p>
    </div>
    <div className="p-6">
      <div className="mb-4 bg-orange-50 p-3 rounded-lg border border-orange-100">
        <span className="font-semibold text-orange-800">Gancho (3 seg):</span> 
        <span className="text-gray-700 ml-2">{script.hook}</span>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-gray-50 rounded-lg p-4 border border-gray-100">
          <h4 className="font-bold text-gray-800 mb-3 flex items-center">
            <span className="bg-gray-200 text-gray-700 p-1 rounded mr-2 text-xs">👁️</span> Visual
          </h4>
          <ul className="space-y-3">
            {script.visual.map((line, i) => (
              <li key={i} className="text-sm text-gray-600 border-l-2 border-orange-300 pl-3 py-1">
                {line}
              </li>
            ))}
          </ul>
        </div>
        <div className="bg-gray-50 rounded-lg p-4 border border-gray-100">
          <h4 className="font-bold text-gray-800 mb-3 flex items-center">
            <span className="bg-gray-200 text-gray-700 p-1 rounded mr-2 text-xs">🎙️</span> Audio
          </h4>
          <ul className="space-y-3">
            {script.audio.map((line, i) => (
              <li key={i} className="text-sm text-gray-700 italic border-l-2 border-gray-300 pl-3 py-1">
                "{line}"
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  </div>
);

const StrategySection = () => (
  <div className="space-y-8 animate-in fade-in duration-500">
    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <h2 className="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Horarios Estratégicos (Bolivia)</h2>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-orange-50 p-4 rounded-lg text-center border border-orange-100">
          <div className="text-orange-500 font-bold text-xl mb-1">06:00 - 07:30</div>
          <div className="font-semibold text-gray-800">La Mañana</div>
          <p className="text-xs text-gray-600 mt-2">Desayuno y espera de carga. Ideal para Hacks rápidos.</p>
        </div>
        <div className="bg-gray-50 p-4 rounded-lg text-center border border-gray-100">
          <div className="text-orange-500 font-bold text-xl mb-1">12:30 - 14:00</div>
          <div className="font-semibold text-gray-800">El Mediodía</div>
          <p className="text-xs text-gray-600 mt-2">Parada en surtidores. Ideal para Comedia y Sketches.</p>
        </div>
        <div className="bg-orange-50 p-4 rounded-lg text-center border border-orange-100">
          <div className="text-orange-500 font-bold text-xl mb-1">19:30 - 21:30</div>
          <div className="font-semibold text-gray-800">La Noche</div>
          <p className="text-xs text-gray-600 mt-2">Fin de jornada. Ideal para Storytimes largos.</p>
        </div>
      </div>
    </div>

    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <h2 className="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Fórmula de Hashtags (3-3-3)</h2>
      <p className="text-gray-600 mb-4 text-sm">Mezcla exactamente 9 hashtags para mantener al algoritmo enfocado sin saturarlo.</p>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="p-4 border border-gray-200 rounded-lg">
          <h4 className="font-bold text-gray-800 mb-2">3 de Nicho</h4>
          <p className="text-orange-500 text-sm font-mono bg-orange-50 p-2 rounded">#TransporteBolivia<br/>#Camioneros<br/>#Logistica</p>
        </div>
        <div className="p-4 border border-gray-200 rounded-lg">
          <h4 className="font-bold text-gray-800 mb-2">3 Locales</h4>
          <p className="text-orange-500 text-sm font-mono bg-orange-50 p-2 rounded">#RutasBolivianas<br/>#SantaCruzBolivia<br/>#Carreteras</p>
        </div>
        <div className="p-4 border border-gray-200 rounded-lg">
          <h4 className="font-bold text-gray-800 mb-2">3 Marca/Solución</h4>
          <p className="text-orange-500 text-sm font-mono bg-orange-50 p-2 rounded">#MulaCargo<br/>#Logtech<br/>#Emprendedores</p>
        </div>
      </div>
    </div>
  </div>
);

const AdsSection = () => (
  <div className="space-y-8 animate-in fade-in duration-500">
    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <h2 className="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Distribución de Presupuesto</h2>
      <div className="flex flex-col md:flex-row gap-6 items-center">
        <div className="w-48 h-48 rounded-full border-8 border-orange-100 flex items-center justify-center relative">
          <svg viewBox="0 0 36 36" className="w-full h-full absolute transform -rotate-90 text-orange-500">
            <path strokeDasharray="80, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" strokeWidth="4" />
          </svg>
          <div className="text-center">
            <div className="text-3xl font-bold text-gray-800">80/20</div>
            <div className="text-xs text-gray-500">Regla Meta Ads</div>
          </div>
        </div>
        <div className="flex-1 space-y-4">
          <div className="bg-orange-50 p-4 rounded-lg border-l-4 border-orange-500">
            <h4 className="font-bold text-gray-800">80% Tráfico Frío (Adquisición)</h4>
            <p className="text-sm text-gray-600 mt-1">Dividido equitativamente entre intereses de PYMES (Emprendimiento, Ventas) e intereses de Transportistas (Camiones, Rutas).</p>
          </div>
          <div className="bg-gray-50 p-4 rounded-lg border-l-4 border-gray-400">
            <h4 className="font-bold text-gray-800">20% Retargeting (Conversión)</h4>
            <p className="text-sm text-gray-600 mt-1">Dirigido a quienes vieron el 50% de los videos orgánicos o visitaron el perfil pero no descargaron la app.</p>
          </div>
        </div>
      </div>
    </div>

    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <h2 className="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">KPIs Principales a Monitorear</h2>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="p-5 bg-white border border-gray-200 rounded-xl shadow-sm text-center">
          <div className="text-orange-500 font-bold text-2xl mb-2">CPI</div>
          <div className="font-semibold text-gray-800">Costo por Instalación</div>
          <p className="text-xs text-gray-500 mt-2">La métrica reina. Cuánto cuesta que un usuario descargue y abra la app.</p>
        </div>
        <div className="p-5 bg-white border border-gray-200 rounded-xl shadow-sm text-center">
          <div className="text-orange-500 font-bold text-2xl mb-2">&gt; 30%</div>
          <div className="font-semibold text-gray-800">Hook Rate</div>
          <p className="text-xs text-gray-500 mt-2">% de personas que vieron más de 3 segundos. Mide la eficacia del gancho inicial.</p>
        </div>
        <div className="p-5 bg-white border border-gray-200 rounded-xl shadow-sm text-center">
          <div className="text-orange-500 font-bold text-2xl mb-2">&gt; 1%</div>
          <div className="font-semibold text-gray-800">CTR</div>
          <p className="text-xs text-gray-500 mt-2">Click-Through Rate. Mide si el Call to Action convence de ir a la App Store.</p>
        </div>
      </div>
    </div>
  </div>
);

const ReferralSection = () => {
  const [referrals, setReferrals] = useState(50);
  const [commission, setCommission] = useState(150);
  const bonusPerReferral = 50; // Bs

  const totalCost = referrals * bonusPerReferral;
  const expectedRevenue = referrals * commission;
  const isProfitable = expectedRevenue >= totalCost;

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 className="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Incentivos "Doble Cara"</h2>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="border border-orange-200 rounded-lg p-5 bg-orange-50">
            <h3 className="font-bold text-orange-800 text-lg mb-3">Lado PYMES (Comerciantes)</h3>
            <ul className="space-y-3 text-sm text-gray-700">
              <li className="flex items-start"><span className="mr-2 text-orange-500">✓</span> <strong>El que invita:</strong> Gana 50 Bs en Crédito Mula (solo canjeable en fletes futuros).</li>
              <li className="flex items-start"><span className="mr-2 text-orange-500">✓</span> <strong>El invitado:</strong> Obtiene 50 Bs de descuento automático en su primer envío.</li>
              <li className="flex items-start"><span className="mr-2 text-orange-500">★</span> <strong>Gamificación:</strong> Referir a 5 da bono extra de 200 Bs en crédito.</li>
            </ul>
          </div>
          <div className="border border-gray-200 rounded-lg p-5 bg-gray-50">
            <h3 className="font-bold text-gray-800 text-lg mb-3">Lado Transportistas (Choferes)</h3>
            <ul className="space-y-3 text-sm text-gray-700">
              <li className="flex items-start"><span className="mr-2 text-gray-500">✓</span> <strong>El que invita:</strong> Gana 50 Bs en Efectivo (directo a la Billetera Digital, retirable).</li>
              <li className="flex items-start"><span className="mr-2 text-gray-500">✓</span> <strong>El invitado:</strong> Obtiene 0% de comisión de app en sus primeros 2 fletes.</li>
              <li className="flex items-start"><span className="mr-2 text-gray-500">★</span> <strong>Gamificación:</strong> Referir a 3 da Prioridad en Pujas (ven cargas antes).</li>
            </ul>
          </div>
        </div>
      </div>

      <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 className="text-2xl font-bold text-gray-800 mb-2">Calculadora ROI de Referidos</h2>
        <p className="text-gray-500 text-sm mb-6">Estima el impacto financiero basado en fletes completados con éxito.</p>
        
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div className="space-y-6">
            <div>
              <div className="flex justify-between mb-1">
                <label className="text-sm font-semibold text-gray-700">Referidos Exitosos al Mes</label>
                <span className="text-sm font-bold text-orange-500">{referrals} usuarios</span>
              </div>
              <input 
                type="range" min="0" max="500" step="5"
                value={referrals} 
                onChange={(e) => setReferrals(parseInt(e.target.value))}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-500"
              />
            </div>
            
            <div>
              <div className="flex justify-between mb-1">
                <label className="text-sm font-semibold text-gray-700">Comisión Promedio por Flete (Bs)</label>
                <span className="text-sm font-bold text-orange-500">{commission} Bs</span>
              </div>
              <input 
                type="range" min="50" max="500" step="10"
                value={commission} 
                onChange={(e) => setCommission(parseInt(e.target.value))}
                className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-500"
              />
            </div>
            
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
              <p className="text-gray-600 mb-1">Costo Fijo por Referido: <strong>{bonusPerReferral} Bs</strong></p>
              <p className="text-xs text-gray-400">*El premio se paga solo si se completa un flete real.</p>
            </div>
          </div>

          <div className="flex flex-col justify-center">
            <div className="bg-white border-2 border-gray-100 rounded-xl overflow-hidden shadow-lg">
              <div className="p-4 border-b border-gray-100 bg-gray-50">
                <h4 className="font-bold text-center text-gray-700">Proyección Mensual</h4>
              </div>
              <div className="p-6 space-y-4">
                <div className="flex justify-between items-end">
                  <span className="text-sm text-gray-500">Costo en Bonos:</span>
                  <span className="text-xl font-bold text-red-500">-{totalCost.toLocaleString()} Bs</span>
                </div>
                <div className="flex justify-between items-end">
                  <span className="text-sm text-gray-500">Ingreso por Comisión:</span>
                  <span className="text-xl font-bold text-green-500">+{expectedRevenue.toLocaleString()} Bs</span>
                </div>
                <div className="w-full h-px bg-gray-200 my-2"></div>
                <div className="flex justify-between items-end">
                  <span className="text-base font-bold text-gray-700">Balance Bruto:</span>
                  <span className={`text-2xl font-black ${isProfitable ? 'text-orange-500' : 'text-red-500'}`}>
                    {(expectedRevenue - totalCost).toLocaleString()} Bs
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default function App() {
  const [activeTab, setActiveTab] = useState('strategy');

  const navItems = [
    { id: 'strategy', label: 'Estrategia Orgánica', icon: Icons.Strategy },
    { id: 'transport', label: 'Guiones Transportistas', icon: Icons.Truck },
    { id: 'smes', label: 'Guiones PYMES', icon: Icons.Store },
    { id: 'ads', label: 'Ads y Paid Media', icon: Icons.Ads },
    { id: 'referrals', label: 'Programa Referidos', icon: Icons.Users },
  ];

  const renderContent = () => {
    switch (activeTab) {
      case 'strategy': return <StrategySection />;
      case 'transport': return (
        <div className="animate-in fade-in duration-500">
          <p className="text-gray-500 mb-6">Guiones enfocados en el dolor del transportista: viajar vacío y rogar por pagos.</p>
          {transportScriptsData.map((script, idx) => <ScriptCard key={idx} script={script} />)}
        </div>
      );
      case 'smes': return (
        <div className="animate-in fade-in duration-500">
          <p className="text-gray-500 mb-6">Guiones enfocados en comerciantes y dueños de negocio hartos de tarifas injustas.</p>
          {smesScriptsData.map((script, idx) => <ScriptCard key={idx} script={script} />)}
        </div>
      );
      case 'ads': return <AdsSection />;
      case 'referrals': return <ReferralSection />;
      default: return <StrategySection />;
    }
  };

  return (
    <div className="flex h-screen bg-gray-100 font-sans text-gray-900 overflow-hidden selection:bg-orange-200 selection:text-orange-900">
      {/* Sidebar Navigation */}
      <aside className="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0 shadow-xl z-10">
        <div className="p-6 bg-gray-950 flex items-center gap-3">
          <div className="w-8 h-8 rounded bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center font-bold shadow-lg shadow-orange-500/20">
            M
          </div>
          <div>
            <h1 className="font-bold text-lg leading-tight tracking-tight text-white">Mula Cargo</h1>
            <p className="text-xs text-orange-400 font-medium tracking-wider uppercase">Marketing Playbook</p>
          </div>
        </div>
        
        <nav className="flex-1 overflow-y-auto py-6">
          <ul className="space-y-1 px-3">
            {navItems.map((item) => (
              <li key={item.id}>
                <button
                  onClick={() => setActiveTab(item.id)}
                  className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 text-sm font-medium ${
                    activeTab === item.id
                      ? 'bg-orange-500 text-white shadow-md'
                      : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100'
                  }`}
                >
                  <item.icon />
                  {item.label}
                </button>
              </li>
            ))}
          </ul>
        </nav>
        
        <div className="p-4 bg-gray-950 border-t border-gray-800">
          <div className="flex items-center gap-2 text-xs text-gray-500">
            <span className="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Sistema Activo - Bolivia
          </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <main className="flex-1 overflow-y-auto bg-gray-50 p-8">
        <div className="max-w-5xl mx-auto">
          <header className="mb-8">
            <h2 className="text-3xl font-extrabold text-gray-900 tracking-tight">
              {navItems.find(i => i.id === activeTab)?.label}
            </h2>
            <div className="w-16 h-1 bg-orange-500 mt-4 rounded-full"></div>
          </header>
          
          <div className="pb-16">
            {renderContent()}
          </div>
        </div>
      </main>
    </div>
  );
}