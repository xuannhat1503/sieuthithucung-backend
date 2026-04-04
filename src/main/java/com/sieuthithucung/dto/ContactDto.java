package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ContactDto {
    private Long id;
    private String fullName;
    private String phoneNumber;
    private String email;
    private String message;
    private String isReplied;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

