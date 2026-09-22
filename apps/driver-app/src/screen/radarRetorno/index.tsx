import React, { useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { styles } from './styles';

export const RadarRetornoScreen: React.FC = () => {
  const [mode, setMode] = useState<'pre_trip' | 'live_gps'>('pre_trip');
  const [origin, setOrigin] = useState('Santa Cruz de la Sierra (Mi Garaje)');
  const [destination, setDestination] = useState('Montero / Parque Industrial');
  const [plannedOutbound, setPlannedOutbound] = useState('Mañana 07:30 AM');
  const [plannedReturn, setPlannedReturn] = useState('Mañana 15:00 PM');
  const [maxDeviation, setMaxDeviation] = useState(15);
  const [isLoading, setIsLoading] = useState(false);
  const [matchedFreights, setMatchedFreights] = useState<any[]>([
    {
      id: 201,
      title: 'Carga Palletizada Textil & Insumos',
      category: 'Camión 3/4 & Toco',
      weight_kg: 2400,
      volume_m3: 14.5,
      pickup: 'Montero (Parque Industrial)',
      dropoff: 'Santa Cruz (4to Anillo)',
      deviation_km: 4.2,
      ready_time: 'Mañana 15:30 PM',
      offered_price: 1250.00,
      platform_fee: 43.75,
      net_earnings: 1206.25,
    },
    {
      id: 202,
      title: 'Alimentos Secos y Bebidas en Caja',
      category: 'Furgón / VUC',
      weight_kg: 1100,
      volume_m3: 8.0,
      pickup: 'Warnes (Centro Logístico)',
      dropoff: 'Santa Cruz (Mercado Abasto)',
      deviation_km: 7.8,
      ready_time: 'Mañana 16:00 PM',
      offered_price: 680.00,
      platform_fee: 23.80,
      net_earnings: 656.20,
    },
    {
      id: 203,
      title: 'Repuestos Automotrices & Herramientas',
      category: 'Utilitario / Pickup',
      weight_kg: 380,
      volume_m3: 1.8,
      pickup: 'Carretera al Norte Km 18',
      dropoff: 'Santa Cruz (Villa 1ro de Mayo)',
      deviation_km: 1.5,
      ready_time: 'Mañana 14:45 PM',
      offered_price: 350.00,
      platform_fee: 12.25,
      net_earnings: 337.75,
    },
  ]);

  const handleSearchCorridor = () => {
    setIsLoading(true);
    setTimeout(() => {
      setIsLoading(false);
      Alert.alert(
        'Radar de Retorno Actualizado',
        `Se encontraron ${matchedFreights.length} cargas compatibles en el corredor de retorno programado.`
      );
    }, 600);
  };

  const handleBookBackhaul = (freight: any) => {
    Alert.alert(
      'Reservar Flete de Retorno',
      `¿Desea bloquear el flete "${freight.title}" para su retorno?\n\nGanancia Neta: Bs. ${freight.net_earnings.toFixed(2)} (Tarifa MulaCargo 3.5% aplicada).`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Confirmar Reserva',
          onPress: () => {
            Alert.alert('¡Retorno Asegurado!', 'Flete reservado con éxito. El remitente ha sido notificado de su hora programada de recogida.');
          },
        },
      ]
    );
  };

  return (
    <View style={styles.container}>
      {/* Top Bar */}
      <View style={styles.headerContainer}>
        <View>
          <Text style={styles.headerTitle}>Radar de Retorno</Text>
          <Text style={styles.headerSub}>Optimización de Flete y Retorno en Vacío</Text>
        </View>
        <View style={styles.statusBadge}>
          <Text style={styles.statusBadgeText}>3.5% Tasa</Text>
        </View>
      </View>

      {/* Mode Switcher */}
      <View style={styles.modeToggleContainer}>
        <TouchableOpacity
          style={[styles.modeTab, mode === 'pre_trip' && styles.modeTabActive]}
          onPress={() => setMode('pre_trip')}
          activeOpacity={0.8}
        >
          <Text style={[styles.modeTabText, mode === 'pre_trip' && styles.modeTabTextActive]}>
            Planificar Antes de Salir
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.modeTab, mode === 'live_gps' && styles.modeTabActive]}
          onPress={() => setMode('live_gps')}
          activeOpacity={0.8}
        >
          <Text style={[styles.modeTabText, mode === 'live_gps' && styles.modeTabTextActive]}>
            Retorno en Vivo (GPS)
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Pre-Trip Configuration Box */}
        <View style={styles.planningCard}>
          <Text style={styles.cardHeaderTitle}>
            {mode === 'pre_trip' ? '🗓️ Planificación Previa del Viaje' : '📍 Retorno Directo a Base'}
          </Text>
          <Text style={styles.cardHeaderDesc}>
            {mode === 'pre_trip'
              ? 'Asegure su carga de retorno antes de encender el motor y elimine el viaje vacío.'
              : 'Detectando fletes disponibles a lo largo de su ruta de retorno hacia su base.'}
          </Text>

          {mode === 'pre_trip' && (
            <>
              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Origen de Salida</Text>
                <TextInput
                  style={styles.inputBox}
                  value={origin}
                  onChangeText={setOrigin}
                  placeholder="Ciudad o garaje de partida"
                  placeholderTextColor="#8C929D"
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Destino de Ida</Text>
                <TextInput
                  style={styles.inputBox}
                  value={destination}
                  onChangeText={setDestination}
                  placeholder="Ciudad o parada de entrega"
                  placeholderTextColor="#8C929D"
                />
              </View>

              <View style={styles.rowInputs}>
                <View style={[styles.inputGroup, { flex: 1, marginRight: 8 }]}>
                  <Text style={styles.inputLabel}>Salida de Ida</Text>
                  <TextInput
                    style={styles.inputBox}
                    value={plannedOutbound}
                    onChangeText={setPlannedOutbound}
                    placeholder="Día y hora"
                    placeholderTextColor="#8C929D"
                  />
                </View>

                <View style={[styles.inputGroup, { flex: 1 }]}>
                  <Text style={styles.inputLabel}>Listo para Retorno</Text>
                  <TextInput
                    style={styles.inputBox}
                    value={plannedReturn}
                    onChangeText={setPlannedReturn}
                    placeholder="Día y hora retorno"
                    placeholderTextColor="#8C929D"
                  />
                </View>
              </View>
            </>
          )}

          <Text style={styles.inputLabel}>Desvío Máximo Aceptado en Ruta</Text>
          <View style={styles.deviationChipsRow}>
            {[5, 15, 30, 50].map((km) => (
              <TouchableOpacity
                key={km}
                style={[styles.chip, maxDeviation === km && styles.chipSelected]}
                onPress={() => setMaxDeviation(km)}
              >
                <Text style={[styles.chipText, maxDeviation === km && styles.chipTextSelected]}>
                  +{km} km
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          <TouchableOpacity
            style={styles.actionButton}
            onPress={handleSearchCorridor}
            activeOpacity={0.8}
            disabled={isLoading}
          >
            {isLoading ? (
              <ActivityIndicator color="#fff" size="small" />
            ) : (
              <Text style={styles.actionButtonText}>
                {mode === 'pre_trip' ? 'Buscar Cargas de Retorno en Corredor' : 'Escanear Retorno Inmediato'}
              </Text>
            )}
          </TouchableOpacity>
        </View>

        {/* Suggested Return Freights Section */}
        <Text style={styles.sectionHeading}>
          Oportunidades de Retorno ({matchedFreights.length})
        </Text>

        {matchedFreights.map((freight) => (
          <View key={freight.id} style={styles.freightCard}>
            <View style={styles.freightTopRow}>
              <View style={styles.categoryBadge}>
                <Text style={styles.categoryBadgeText}>{freight.category}</Text>
              </View>
              <Text style={styles.deviationTag}>Desvío: +{freight.deviation_km} km</Text>
            </View>

            <Text style={styles.freightTitle}>{freight.title}</Text>

            <View style={styles.routeRow}>
              <View style={styles.routeDot} />
              <Text style={styles.routeText}>Recogida: {freight.pickup}</Text>
            </View>
            <View style={styles.routeRow}>
              <View style={styles.routeDotDrop} />
              <Text style={styles.routeText}>Entrega: {freight.dropoff}</Text>
            </View>

            <View style={styles.specsRow}>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>PESO</Text>
                <Text style={styles.specVal}>{freight.weight_kg} kg</Text>
              </View>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>VOLUMEN</Text>
                <Text style={styles.specVal}>{freight.volume_m3} m³</Text>
              </View>
              <View style={styles.specItem}>
                <Text style={styles.specLabel}>HORA RETORNO</Text>
                <Text style={styles.specVal}>{freight.ready_time}</Text>
              </View>
            </View>

            {/* Price & Platform Fee Transparency Box */}
            <View style={styles.priceBreakdownBox}>
              <View style={styles.priceRow}>
                <Text style={styles.priceLabel}>Flete Ofertado:</Text>
                <Text style={styles.priceValue}>Bs. {freight.offered_price.toFixed(2)}</Text>
              </View>
              <View style={styles.priceRow}>
                <Text style={styles.priceLabel}>Comisión MulaCargo (3.5%):</Text>
                <Text style={styles.priceValue}>-Bs. {freight.platform_fee.toFixed(2)}</Text>
              </View>
              <View style={[styles.priceRow, { marginTop: 4 }]}>
                <Text style={styles.netEarningsLabel}>Ganancia Neta Conductor:</Text>
                <Text style={styles.netEarningsValue}>Bs. {freight.net_earnings.toFixed(2)}</Text>
              </View>
            </View>

            <TouchableOpacity
              style={styles.bidButton}
              onPress={() => handleBookBackhaul(freight)}
              activeOpacity={0.8}
            >
              <Text style={styles.bidButtonText}>⚡ Asegurar Flete de Retorno</Text>
            </TouchableOpacity>
          </View>
        ))}
      </ScrollView>
    </View>
  );
};

export default RadarRetornoScreen;
