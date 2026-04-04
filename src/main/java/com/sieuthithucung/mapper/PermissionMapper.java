package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PermissionDto;
import com.sieuthithucung.entity.PermissionEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class PermissionMapper extends BaseMapper<PermissionEntity, PermissionDto> {

    public PermissionMapper(ObjectMapper objectMapper) {
        super(objectMapper, PermissionEntity.class, PermissionDto.class);
    }
}

