import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  Image,
  Alert,
} from 'react-native';

interface BidItem {
  id: number;
  carrierName: string;
  carrierRating: number;
  completedTrips: number;
  vehicleType: string;
  vehiclePlate: string;
  bidAmount: number;
  isBackhaulReturn: boolean;
  etaMinutes: number;
}

interface MulaCargoBiddingStreamProps {
  initialPrice?: number;
  onAcceptBid?: (bid: BidItem) => void;
}

export const MulaCargoBiddingStream: React.FC<MulaCargoBiddingStreamProps> = ({
  initialPrice = 1200,
  onAcceptBid,
}) => {
  const [counterAmount, setCounterAmount] = useState<string>('');
  const [bids, setBids] = useState<BidItem[]>([
    {
      id: 1,
      carrierName: 'Carlos M. Vaca',
      carrierRating: 4.9,
      completedTrips: 184,
      vehicleType: 'Camión 3/4 (5T)',
      vehiclePlate: '4821-EFT',
      bidAmount: 1100,
      isBackhaulReturn: true, // Radar de Retorno match
      etaMinutes: 25,
    },
    {
      id: 2,
      carrierName: 'Transportes Quiroz & Hnos',
      carrierRating: 5.0,
      completedTrips: 340,
      vehicleType: 'Furgón Sprinter (2T)',
      vehiclePlate: '3190-KLX',
      bidAmount: 1250,
      isBackhaulReturn: false,
      etaMinutes: 15,
    },
    {
      id: 3,
      carrierName: 'Jorge R. Justiniano',
      carrierRating: 4.8,
      completedTrips: 92,
      vehicleType: 'Pickup Hilux Carga',
      vehiclePlate: '2049-ABC',
      bidAmount: 950,
      isBackhaulReturn: true,
      etaMinutes: 40,
    },
  ]);

  const handleAccept = (bid: BidItem) => {
    Alert.alert(
      'Aceptar Oferta de Flete',
      `¿Desea adjudicar el flete a ${bid.carrierName} por Bs. ${bid.bidAmount.toFixed(2)}?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Confirmar y Adjudicar',
          onPress: () => {
            if (onAcceptBid) onAcceptBid(bid);
            Alert.alert('¡Flete Adjudicado!', `${bid.carrierName} está en camino para la recogida.`);
          },
        },
      ]
    );
  };

  const handleSendCounter = (bidId: number) => {
    const parsed = parseFloat(counterAmount);
    if (!parsed || parsed <= 0) {
      Alert.alert('Monto inválido', 'Ingrese un monto válido para la contraoferta.');
      return;
    }
    Alert.alert(
      'Contraoferta Enviada',
      `Se envió su contraoferta de Bs. ${parsed.toFixed(2)} al transportista.`
    );
    setCounterAmount('');
  };

  return (
    <View style={styles.container}>
      <View style={styles.streamHeader}>
        <View>
          <Text style={styles.headerTitle}>Sala de Negociación en Vivo</Text>
          <Text style={styles.headerSub}>Ofertas de transportistas en tiempo real</Text>
        </View>
        <View style={styles.liveIndicator}>
          <View style={styles.liveDot} />
          <Text style={styles.liveText}>EN DIRECTO</Text>
        </View>
      </View>

      {bids.map((bid) => (
        <View key={bid.id} style={styles.bidCard}>
          <View style={styles.cardTop}>
            <View style={styles.carrierInfo}>
              <Text style={styles.carrierName}>{bid.carrierName}</Text>
              <View style={styles.ratingRow}>
                <Text style={styles.ratingText}>⭐ {bid.carrierRating}</Text>
                <Text style={styles.tripsText}>({bid.completedTrips} viajes)</Text>
              </View>
            </View>

            <View style={styles.priceColumn}>
              <Text style={styles.bidAmount}>Bs. {bid.bidAmount.toFixed(2)}</Text>
              {bid.isBackhaulReturn && (
                <View style={styles.backhaulBadge}>
                  <Text style={styles.backhaulBadgeText}>⚡ RETORNO RADAR</Text>
                </View>
              )}
            </View>
          </View>

          <View style={styles.vehicleDetailsRow}>
            <Text style={styles.vehicleText}>🚛 {bid.vehicleType} • {bid.vehiclePlate}</Text>
            <Text style={styles.etaText}>⏱️ Llega en {bid.etaMinutes} min</Text>
          </View>

          {/* Action Row */}
          <View style={styles.actionRow}>
            <TouchableOpacity
              style={styles.acceptButton}
              onPress={() => handleAccept(bid)}
              activeOpacity={0.8}
            >
              <Text style={styles.acceptButtonText}>Aceptar por Bs. {bid.bidAmount}</Text>
            </TouchableOpacity>
          </View>
        </View>
      ))}

      {/* Global Counter-Offer Input */}
      <View style={styles.counterBox}>
        <Text style={styles.counterTitle}>Proponer Contraoferta a Todos los Conductores</Text>
        <View style={styles.counterInputRow}>
          <TextInput
            style={styles.counterInput}
            value={counterAmount}
            onChangeText={setCounterAmount}
            placeholder="Ej. 1050"
            placeholderTextColor="#8C929D"
            keyboardType="numeric"
          />
          <TouchableOpacity
            style={styles.counterSubmitButton}
            onPress={() => handleSendCounter(0)}
            activeOpacity={0.8}
          >
            <Text style={styles.counterSubmitText}>Enviar Oferta</Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#0A0A0C',
    padding: 16,
    borderRadius: 12,
  },
  streamHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  headerSub: {
    fontSize: 12,
    color: '#8C929D',
    marginTop: 2,
  },
  liveIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 85, 0, 0.15)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255, 85, 0, 0.4)',
  },
  liveDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#FF5500',
    marginRight: 6,
  },
  liveText: {
    fontSize: 10,
    fontWeight: '800',
    color: '#FF5500',
    letterSpacing: 0.5,
  },
  bidCard: {
    backgroundColor: '#12141A',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    padding: 14,
    marginBottom: 12,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  carrierInfo: {
    flex: 1,
  },
  carrierName: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
  },
  ratingText: {
    fontSize: 12,
    color: '#D4AF37',
    fontWeight: '600',
    marginRight: 6,
  },
  tripsText: {
    fontSize: 11,
    color: '#8C929D',
  },
  priceColumn: {
    alignItems: 'flex-end',
  },
  bidAmount: {
    fontSize: 18,
    fontWeight: '800',
    color: '#00E599',
  },
  backhaulBadge: {
    backgroundColor: 'rgba(0, 229, 153, 0.15)',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
    marginTop: 4,
  },
  backhaulBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: '#00E599',
  },
  vehicleDetailsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: '#181C26',
    borderRadius: 6,
    padding: 8,
    marginTop: 10,
    marginBottom: 12,
  },
  vehicleText: {
    fontSize: 12,
    color: '#E0E6ED',
  },
  etaText: {
    fontSize: 12,
    color: '#D4AF37',
    fontWeight: '600',
  },
  actionRow: {
    flexDirection: 'row',
    gap: 8,
  },
  acceptButton: {
    flex: 1,
    backgroundColor: '#FF5500',
    paddingVertical: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  acceptButtonText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 13,
  },
  counterBox: {
    backgroundColor: '#12141A',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    padding: 12,
    marginTop: 6,
  },
  counterTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: '#E0E6ED',
    marginBottom: 8,
  },
  counterInputRow: {
    flexDirection: 'row',
    gap: 8,
  },
  counterInput: {
    flex: 1,
    backgroundColor: '#181C26',
    borderRadius: 6,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    paddingHorizontal: 10,
    paddingVertical: 8,
    color: '#FFFFFF',
    fontSize: 14,
  },
  counterSubmitButton: {
    backgroundColor: '#D4AF37',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 6,
    justifyContent: 'center',
    alignItems: 'center',
  },
  counterSubmitText: {
    color: '#0A0A0C',
    fontWeight: '700',
    fontSize: 12,
  },
});

export default MulaCargoBiddingStream;
