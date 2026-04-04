package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.RoleDto;
import com.sieuthithucung.entity.RoleEntity;
import com.sieuthithucung.mapper.RoleMapper;
import com.sieuthithucung.repository.RoleRepository;
import org.springframework.stereotype.Service;

@Service
public class RoleService extends AbstractCrudService<RoleEntity, Long, RoleDto> {

    public RoleService(RoleRepository repository, RoleMapper mapper) {
        super(repository, mapper, "Role");
    }
}

