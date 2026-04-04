package com.sieuthithucung.dto;

import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
public class PaymentDto {
    private Long id;
    private Long orderId;
    private String paymentMethod;
    private String transactionId;
    private String status;
    private LocalDateTime paidAy;
    private BigDecimal amount;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

