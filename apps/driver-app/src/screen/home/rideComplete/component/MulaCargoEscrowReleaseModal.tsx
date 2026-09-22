import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  Alert,
} from 'react-native';
import appColors from '../../../../theme/appColors';

interface EscrowReleaseModalProps {
  visible: boolean;
  onClose: () => void;
  freightTitle?: string;
  grossAmount?: number;
  onReleaseSuccess?: (netEarnings: number) => void;
}

export const MulaCargoEscrowReleaseModal: React.FC<EscrowReleaseModalProps> = ({
  visible,
  onClose,
  freightTitle = 'Carga Palletizada Textil & Insumos',
  grossAmount = 1250,
  onReleaseSuccess,
}) => {
  const [otp, setOtp] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSettled, setIsSettled] = useState(false);

  const platformFee = grossAmount * 0.035; // 3.5%
  const netEarnings = grossAmount - platformFee;

  const handleVerifyAndRelease = () => {
    if (otp.length < 4) {
      Alert.alert('Código Incompleto', 'Solicite al receptor el código OTP de 4 dígitos para validar la entrega.');
      return;
    }

    setIsSubmitting(true);
    setTimeout(() => {
      setIsSubmitting(false);
      setIsSettled(true);
      Alert.alert(
        '¡Fondos Liquidados en su Billetera!',
        `Entrega validada con éxito.\n\nFlete Bruto: Bs. ${grossAmount.toFixed(2)}\nComisión MulaCargo (3.5%): -Bs. ${platformFee.toFixed(2)}\n\nGanancia Acreditada: Bs. ${netEarnings.toFixed(2)}`
      );
      if (onReleaseSuccess) onReleaseSuccess(netEarnings);
    }, 750);
  };

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.card}>
          <Text style={styles.title}>📦 Validación de Entrega & Liquidación</Text>
          <Text style={styles.sub}>{freightTitle}</Text>

          {!isSettled ? (
            <>
              <View style={styles.instructionBox}>
                <Text style={styles.instructionText}>
                  Solicite al <strong>Receptor de la Carga</strong> el código OTP de entrega para desbloquear la custodia de fondos (Escrow).
                </Text>
              </View>

              <Text style={styles.otpLabel}>INGRESE EL CÓDIGO OTP (4 DÍGITOS)</Text>
              <TextInput
                style={styles.otpInput}
                value={otp}
                onChangeText={setOtp}
                keyboardType="numeric"
                maxLength={4}
                placeholder="••••"
                placeholderTextColor="#8C929D"
              />

              {/* Fee Transparency Box */}
              <View style={styles.breakdownBox}>
                <View style={styles.breakdownRow}>
                  <Text style={styles.breakdownLabel}>Flete Acordado:</Text>
                  <Text style={styles.breakdownVal}>Bs. {grossAmount.toFixed(2)}</Text>
                </View>
                <View style={styles.breakdownRow}>
                  <Text style={styles.breakdownLabel}>Tarifa MulaCargo (3.5%):</Text>
                  <Text style={styles.feeVal}>-Bs. {platformFee.toFixed(2)}</Text>
                </View>
                <View style={[styles.breakdownRow, { marginTop: 4, borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.1)', paddingTop: 4 }]}>
                  <Text style={styles.netLabel}>Su Ganancia Neta:</Text>
                  <Text style={styles.netVal}>Bs. {netEarnings.toFixed(2)}</Text>
                </View>
              </View>

              <TouchableOpacity
                style={styles.submitBtn}
                onPress={handleVerifyAndRelease}
                disabled={isSubmitting}
                activeOpacity={0.8}
              >
                {isSubmitting ? (
                  <ActivityIndicator color="#0A0A0C" />
                ) : (
                  <Text style={styles.submitBtnText}>🔓 Validar Entrega & Cobrar</Text>
                )}
              </TouchableOpacity>
            </>
          ) : (
            <View style={styles.settledView}>
              <Text style={styles.settledTitle}>🎉 ¡Liquidación Exitosa!</Text>
              <Text style={styles.settledAmount}>+Bs. {netEarnings.toFixed(2)}</Text>
              <Text style={styles.settledNote}>Fondos disponibles para retiro inmediato en su cuenta bancaria.</Text>

              <TouchableOpacity style={styles.closeBtn} onPress={onClose} activeOpacity={0.8}>
                <Text style={styles.closeBtnText}>Volver al Radar de Retorno</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  card: {
    backgroundColor: '#12141A',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 85, 0, 0.3)',
    padding: 20,
    width: '100%',
    maxWidth: 380,
  },
  title: {
    fontSize: 16,
    fontWeight: '800',
    color: '#FFFFFF',
    textAlign: 'center',
  },
  sub: {
    fontSize: 12,
    color: '#8C929D',
    textAlign: 'center',
    marginTop: 4,
    marginBottom: 16,
  },
  instructionBox: {
    backgroundColor: 'rgba(255, 85, 0, 0.1)',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'rgba(255, 85, 0, 0.25)',
    padding: 10,
    marginBottom: 16,
  },
  instructionText: {
    fontSize: 12,
    color: '#E0E6ED',
    lineHeight: 17,
  },
  otpLabel: {
    fontSize: 11,
    color: '#D4AF37',
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 6,
    letterSpacing: 0.5,
  },
  otpInput: {
    backgroundColor: '#181C26',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.15)',
    color: '#FFFFFF',
    fontSize: 28,
    fontWeight: '900',
    textAlign: 'center',
    letterSpacing: 8,
    paddingVertical: 8,
    marginBottom: 16,
  },
  breakdownBox: {
    backgroundColor: '#181C26',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
  },
  breakdownRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  breakdownLabel: {
    fontSize: 12,
    color: '#8C929D',
  },
  breakdownVal: {
    fontSize: 12,
    color: '#E0E6ED',
    fontWeight: '600',
  },
  feeVal: {
    fontSize: 12,
    color: '#FF5500',
    fontWeight: '600',
  },
  netLabel: {
    fontSize: 13,
    color: '#00E599',
    fontWeight: '700',
  },
  netVal: {
    fontSize: 15,
    color: '#00E599',
    fontWeight: '800',
  },
  submitBtn: {
    backgroundColor: '#00E599',
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
  },
  submitBtnText: {
    color: '#0A0A0C',
    fontWeight: '800',
    fontSize: 14,
  },
  settledView: {
    alignItems: 'center',
    paddingVertical: 10,
  },
  settledTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#00E599',
    marginBottom: 8,
  },
  settledAmount: {
    fontSize: 32,
    fontWeight: '900',
    color: '#FFFFFF',
    marginBottom: 8,
  },
  settledNote: {
    fontSize: 12,
    color: '#8C929D',
    textAlign: 'center',
    marginBottom: 16,
  },
  closeBtn: {
    backgroundColor: '#FF5500',
    borderRadius: 8,
    paddingVertical: 12,
    paddingHorizontal: 20,
    width: '100%',
    alignItems: 'center',
  },
  closeBtnText: {
    color: '#FFFFFF',
    fontWeight: '800',
    fontSize: 14,
  },
});

export default MulaCargoEscrowReleaseModal;
