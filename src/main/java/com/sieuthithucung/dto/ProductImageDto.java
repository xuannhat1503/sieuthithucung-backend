package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ProductImageDto {
    private Long id;
    private Long productId;
    private String image;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

