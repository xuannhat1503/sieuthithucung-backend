package com.sieuthithucung.service;

import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.dto.UserRegisterDto;

public interface UserService extends CrudService<Long, UserDto> {
    UserDto register(UserRegisterDto registerDto);
}