package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.entity.UserEntity;
import com.sieuthithucung.mapper.UserMapper;
import com.sieuthithucung.repository.UserRepository;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;

@Service
public class UserService extends AbstractCrudService<UserEntity, Long, UserDto> {

    public UserService(UserRepository repository, UserMapper mapper) {
        super(repository, mapper, "User");
    }

    @Override
    public UserDto create(UserDto dto) {
        return sanitize(super.create(dto));
    }

    @Override
    public UserDto update(Long id, UserDto dto) {
        return sanitize(super.update(id, dto));
    }

    @Override
    public UserDto findById(Long id) {
        return sanitize(super.findById(id));
    }

    @Override
    public Page<UserDto> findAll(Pageable pageable) {
        return super.findAll(pageable).map(this::sanitize);
    }

    private UserDto sanitize(UserDto dto) {
        if (dto != null) {
            dto.setPassword(null);
        }
        return dto;
    }
}

