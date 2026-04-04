package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class OrderDto {
    private Long id;
    private Long userId;
    private BigDecimal totalPrice;
    private String status;
    private Long shippingAddressId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

