package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.entity.UserEntity;
import org.springframework.stereotype.Component;

@Component
public class UserMapper extends BaseMapper<UserEntity, UserDto> {

    public UserMapper(ObjectMapper objectMapper) {
        super(objectMapper, UserEntity.class, UserDto.class);
    }
}

