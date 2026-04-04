package com.sieuthithucung.dto;

import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
public class OrderDto {
    private Long id;
    private Long userId;
    private BigDecimal totalPrice;
    private String status;
    private Long shippingAddressId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

