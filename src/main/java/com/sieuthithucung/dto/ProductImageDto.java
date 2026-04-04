package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class ProductImageDto {
    private Long id;
    private Long productId;
    private String image;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

