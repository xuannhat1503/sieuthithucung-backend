package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.PermissionDto;
import com.sieuthithucung.entity.PermissionEntity;
import com.sieuthithucung.mapper.PermissionMapper;
import com.sieuthithucung.repository.PermissionRepository;
import org.springframework.stereotype.Service;

@Service
public class PermissionService extends AbstractCrudService<PermissionEntity, Long, PermissionDto> {

    public PermissionService(PermissionRepository repository, PermissionMapper mapper) {
        super(repository, mapper, "Permission");
    }
}

