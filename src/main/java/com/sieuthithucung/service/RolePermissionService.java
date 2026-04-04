package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.RolePermissionDto;
import com.sieuthithucung.entity.RolePermissionEntity;
import com.sieuthithucung.mapper.RolePermissionMapper;
import com.sieuthithucung.repository.RolePermissionRepository;
import org.springframework.stereotype.Service;

@Service
public class RolePermissionService extends AbstractCrudService<RolePermissionEntity, Long, RolePermissionDto> {

    public RolePermissionService(RolePermissionRepository repository, RolePermissionMapper mapper) {
        super(repository, mapper, "Role permission");
    }
}

