import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  Image,
  ActivityIndicator,
  Alert,
} from 'react-native';

interface SimpleQrEscrowProps {
  visible: boolean;
  onClose: () => void;
  freightId: number;
  amount: number;
  carrierName?: string;
  onPaymentSuccess?: () => void;
}

export const MulaCargoSimpleQrEscrowModal: React.FC<SimpleQrEscrowProps> = ({
  visible,
  onClose,
  freightId,
  amount,
  carrierName = 'Carlos M. Vaca',
  onPaymentSuccess,
}) => {
  const [escrowStatus, setEscrowStatus] = useState<'pending_qr' | 'escrow_funded' | 'released'>('pending_qr');
  const [isVerifying, setIsVerifying] = useState(false);
  const bankReference = `MULA-SCZ-${freightId}-948F`;
  const deliveryOtp = '7429';

  const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=00020101021226480010BO.GOB.ASOBAN0112MULA_CARGO_BO520459995303068540${amount.toFixed(2)}5802BO5912MULACARGO_SRL6011SANTA_CRUZ62240120${bankReference}6304ABCD`;

  const handleSimulatePayment = () => {
    setIsVerifying(true);
    setTimeout(() => {
      setIsVerifying(false);
      setEscrowStatus('escrow_funded');
      Alert.alert(
        '¡Pago QR Confirmado en Custodia Escrow!',
        `Se han recibido Bs. ${amount.toFixed(2)}. Los fondos están 100% protegidos y solo se liberarán al transportista (${carrierName}) cuando su receptor confirme la entrega con el código OTP.`
      );
      if (onPaymentSuccess) onPaymentSuccess();
    }, 800);
  };

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          {/* Header */}
          <View style={styles.modalHeader}>
            <View>
              <Text style={styles.modalTitle}>Pago con QR Bolivia (Escrow)</Text>
              <Text style={styles.modalSub}>Custodia segura de fondos interbancarios</Text>
            </View>
            <TouchableOpacity style={styles.closeBtn} onPress={onClose}>
              <Text style={styles.closeBtnText}>✕</Text>
            </TouchableOpacity>
          </View>

          {/* Status Badge */}
          <View style={[
            styles.statusPill,
            escrowStatus === 'escrow_funded' ? styles.statusFunded : styles.statusPending
          ]}>
            <Text style={[
              styles.statusPillText,
              escrowStatus === 'escrow_funded' ? styles.statusFundedText : styles.statusPendingText
            ]}>
              {escrowStatus === 'escrow_funded'
                ? '🔒 FONDOS EN CUSTODIA ESCROW'
                : '⏳ ESPERANDO PAGO QR INTERBANCARIO'}
            </Text>
          </View>

          {escrowStatus === 'pending_qr' ? (
            <View style={styles.qrSection}>
              {/* QR Image Frame */}
              <View style={styles.qrFrame}>
                <Image source={{ uri: qrImageUrl }} style={styles.qrImage} />
              </View>

              <Text style={styles.amountText}>Bs. {amount.toFixed(2)}</Text>
              <Text style={styles.feeNote}>Incluye 3.5% tarifa de servicio y seguro de carga</Text>

              {/* Bank Ref Box */}
              <View style={styles.refBox}>
                <Text style={styles.refLabel}>REFERENCIA BANCARIA</Text>
                <Text style={styles.refVal}>{bankReference}</Text>
              </View>

              {/* Bank List */}
              <Text style={styles.bankList}>
                Compatible con: Banco Unión • BCP • BNB • Mercantil Santa Cruz • Banco FIE • Banco BISA • Ganadero
              </Text>

              <TouchableOpacity
                style={styles.verifyBtn}
                onPress={handleSimulatePayment}
                disabled={isVerifying}
                activeOpacity={0.8}
              >
                {isVerifying ? (
                  <ActivityIndicator color="#0A0A0C" />
                ) : (
                  <Text style={styles.verifyBtnText}>⚡ Ya realicé la transferencia QR</Text>
                )}
              </TouchableOpacity>
            </View>
          ) : (
            <View style={styles.fundedSection}>
              <View style={styles.fundedCard}>
                <Text style={styles.fundedTitle}>✅ Custodia Activa</Text>
                <Text style={styles.fundedDesc}>
                  El transportista ya ha recibido la confirmación de viaje asegurado. Al llegar al destino, comparta este código OTP con el receptor para autorizar la descarga:
                </Text>

                <View style={styles.otpBox}>
                  <Text style={styles.otpLabel}>CÓDIGO OTP DE ENTREGA</Text>
                  <Text style={styles.otpValue}>{deliveryOtp}</Text>
                  <Text style={styles.otpSub}>Entregar al transportista únicamente al verificar la carga</Text>
                </View>
              </View>

              <TouchableOpacity style={styles.doneBtn} onPress={onClose} activeOpacity={0.8}>
                <Text style={styles.doneBtnText}>Ver Rastreo en Mapa</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.75)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#12141A',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    padding: 20,
    maxHeight: '90%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  modalTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: '#FFFFFF',
  },
  modalSub: {
    fontSize: 12,
    color: '#8C929D',
    marginTop: 2,
  },
  closeBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#181C26',
    justifyContent: 'center',
    alignItems: 'center',
  },
  closeBtnText: {
    color: '#8C929D',
    fontSize: 16,
    fontWeight: '700',
  },
  statusPill: {
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 20,
    alignItems: 'center',
    marginBottom: 16,
  },
  statusPending: {
    backgroundColor: 'rgba(255, 85, 0, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255, 85, 0, 0.4)',
  },
  statusPendingText: {
    color: '#FF5500',
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  statusFunded: {
    backgroundColor: 'rgba(0, 229, 153, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(0, 229, 153, 0.4)',
  },
  statusFundedText: {
    color: '#00E599',
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  qrSection: {
    alignItems: 'center',
  },
  qrFrame: {
    backgroundColor: '#FFFFFF',
    padding: 12,
    borderRadius: 12,
    marginBottom: 12,
  },
  qrImage: {
    width: 220,
    height: 220,
  },
  amountText: {
    fontSize: 24,
    fontWeight: '800',
    color: '#00E599',
  },
  feeNote: {
    fontSize: 11,
    color: '#8C929D',
    marginTop: 2,
    marginBottom: 12,
  },
  refBox: {
    backgroundColor: '#181C26',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    paddingVertical: 8,
    paddingHorizontal: 14,
    alignItems: 'center',
    width: '100%',
    marginBottom: 10,
  },
  refLabel: {
    fontSize: 10,
    color: '#D4AF37',
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  refVal: {
    fontSize: 13,
    color: '#FFFFFF',
    fontWeight: '700',
    marginTop: 2,
  },
  bankList: {
    fontSize: 11,
    color: '#8C929D',
    textAlign: 'center',
    lineHeight: 16,
    marginBottom: 16,
  },
  verifyBtn: {
    backgroundColor: '#00E599',
    borderRadius: 8,
    paddingVertical: 12,
    width: '100%',
    alignItems: 'center',
  },
  verifyBtnText: {
    color: '#0A0A0C',
    fontWeight: '800',
    fontSize: 14,
  },
  fundedSection: {
    alignItems: 'center',
    paddingVertical: 10,
  },
  fundedCard: {
    backgroundColor: '#181C26',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(0, 229, 153, 0.3)',
    padding: 16,
    width: '100%',
    marginBottom: 16,
  },
  fundedTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#00E599',
    marginBottom: 6,
  },
  fundedDesc: {
    fontSize: 13,
    color: '#E0E6ED',
    lineHeight: 18,
    marginBottom: 16,
  },
  otpBox: {
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.4)',
    padding: 12,
    alignItems: 'center',
  },
  otpLabel: {
    fontSize: 11,
    color: '#D4AF37',
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  otpValue: {
    fontSize: 32,
    fontWeight: '900',
    color: '#FFFFFF',
    letterSpacing: 6,
    marginVertical: 4,
  },
  otpSub: {
    fontSize: 11,
    color: '#8C929D',
    textAlign: 'center',
  },
  doneBtn: {
    backgroundColor: '#FF5500',
    borderRadius: 8,
    paddingVertical: 12,
    width: '100%',
    alignItems: 'center',
  },
  doneBtnText: {
    color: '#FFFFFF',
    fontWeight: '800',
    fontSize: 14,
  },
});

export default MulaCargoSimpleQrEscrowModal;
