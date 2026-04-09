package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class UserRegisterDto {
    private String name;
    private String email;
    private String password;
    private String phoneNumber;
    private String address;
}
