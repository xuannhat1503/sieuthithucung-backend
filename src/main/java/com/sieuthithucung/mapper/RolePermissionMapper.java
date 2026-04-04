package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.RolePermissionDto;
import com.sieuthithucung.entity.RolePermissionEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class RolePermissionMapper extends BaseMapper<RolePermissionEntity, RolePermissionDto> {

    public RolePermissionMapper(ObjectMapper objectMapper) {
        super(objectMapper, RolePermissionEntity.class, RolePermissionDto.class);
    }
}

