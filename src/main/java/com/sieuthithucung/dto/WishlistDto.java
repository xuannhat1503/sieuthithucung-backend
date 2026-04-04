package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class WishlistDto {
    private Long id;
    private Long userId;
    private Long productId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

