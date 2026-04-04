package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class WishlistDto {
    private Long id;
    private Long userId;
    private Long productId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

