package com.sieuthithucung.dto;

import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
public class ProductDto {
    private Long id;
    private String name;
    private String slug;
    private Long categoryId;
    private String description;
    private BigDecimal price;
    private Integer stock;
    private String status;
    private String unit;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

