package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class OrderStatusHistoryDto {
    private Long id;
    private Long orderId;
    private String status;
    private String note;
    private LocalDateTime changedAt;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

