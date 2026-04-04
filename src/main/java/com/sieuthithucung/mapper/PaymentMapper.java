package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PaymentDto;
import com.sieuthithucung.entity.PaymentEntity;

public class PaymentMapper {
    public static PaymentDto mapToPaymentDto(PaymentEntity entity) {
        return new PaymentDto(
                entity.getId(),
                entity.getOrderId(),
                entity.getPaymentMethod(),
                entity.getTransactionId(),
                entity.getStatus(),
                entity.getPaidAy(),
                entity.getAmount(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static PaymentEntity mapToPaymentEntity(PaymentDto dto) {
        return new PaymentEntity(
                dto.getId(),
                dto.getOrderId(),
                dto.getPaymentMethod(),
                dto.getTransactionId(),
                dto.getStatus(),
                dto.getPaidAy(),
                dto.getAmount(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}