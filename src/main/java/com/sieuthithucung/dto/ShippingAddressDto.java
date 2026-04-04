package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ShippingAddressDto {
    private Long id;
    private Long userId;
    private String fullName;
    private String phone;
    private String address;
    private String city;
    private Boolean defaultAddress;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

