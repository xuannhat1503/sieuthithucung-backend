package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.RoleDto;
import com.sieuthithucung.entity.RoleEntity;
import org.springframework.stereotype.Component;

@Component
public class RoleMapper extends BaseMapper<RoleEntity, RoleDto> {

    public RoleMapper(ObjectMapper objectMapper) {
        super(objectMapper, RoleEntity.class, RoleDto.class);
    }
}

